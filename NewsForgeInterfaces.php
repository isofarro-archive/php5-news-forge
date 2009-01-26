<?php

abstract class NewsForgeApi {
	protected $domain;
	protected $currUrl;
	
	abstract public function getStory($dom);
	abstract public function getStories($dom);

	public function getDomain() {
		return $this->domain;
	}
	
	public function setDomain($domain) {
		$this->domain = $domain;
	}
	
	public function setUrl($url) {
		$this->currUrl = $url;
	}
	
	public function normaliseLink($link) {
		//echo "INFO: Normalising link $link\n";
		if (strpos('/', $link) == 0) {
			// Fixed from root. Prefix with domain:
			$link = 'http://' . $this->domain . $link;
		} elseif (strpos('http://', $link) == 0) {
			// Already an absolute URL. Do nothing
		} else {
			echo "WARN: relative URL: $link\n";
			// TODO: get the current URL
		}
		//echo "INFO: Normalised $link\n";
		return $link;
	}	
}

class ReutersStory extends NewsForgeStory {
	// Use the print article URL as the one to parse
	public function getParseStoryLink() {
		return 'http://uk.reuters.com/articlePrint?articleId=' . $this->guid;
	}
	
	public function normaliseTitle($title) {
		$title = preg_replace(
			array('/^CORRECTED\s*-/',		'/^\(OFFICIAL\) /', 
					'/^UPDATE \d+-/',		'/^Corrected: /',
					'/^RPT-UPDATE \d+-/' ),
			array('', '', '', '', ''),		
			$title
		);
		return trim($title);
	}
}

class UkReutersForge extends NewsForgeApi {
	protected $timePattern      = '/(\d{2}:\d{2} \w{2} \w{3})/';
	protected $storyLinkPattern = '/\/article\/([^\/]+)\/id(.*)$/';

	/**
	*	Returns the story data found in the DOM
	**/
	public function getStory($dom, $story=NULL) {
		//echo "INFO: Looking for story data\n";
		if (is_null($story)) {
			$story = new ReutersStory();
		}
		
		// Get the story title
		$title = $dom->find('h1', 0);
		$story->setTitle($title->plaintext);
		
		// Get the published date
		$published = $dom->find('div.timestamp', 0);
		$timestamp = strtotime($published->plaintext);
		$story->setPublished(date('c', $timestamp));
		
		// Get all the paragraphs
		$paras = $dom->find('div.article p');
		array_shift($paras);
		
		$isIntro = true;
		$isEnd   = false;
		
		$buffer  = array();
		foreach($paras as $para) {
			$text = $para->plaintext;
			$isPara = true;
			
			if ($isIntro) {
				if (preg_match('/^&copy; /', $text)) {
					$isPara = false;
				} elseif (preg_match('/^By (.*)$/', $text, $matches)) {
					$isPara = false;
					// TODO: Normalise authors
					$story->setAuthor($matches[1]);
				} elseif (preg_match('/^[A-Z]{2,}/', $text)) {
					// Starts with an uppercase word
					$sep     = strpos($text, ') -');
					$text    = substr($text, $sep+4);
					$isIntro = false;
				}
			} elseif (strpos($text, '&copy;')===0) {
				// Drop copyright notices
				$isPara = false;
			} elseif (strpos($text, '(Reporting')===0) {
				$isPara = false;
				if (preg_match('/by ([^;)]+)/', $text, $matches)) {
					// TODO: this should allow multiple authors
					$story->setAuthor($matches[1]);	
				}
			} elseif (strpos($text, '(Additional reporting')===0) {
				// TODO: Deal with contributors
				$isPara = false;
			} elseif (preg_match('/^([A-Z ]+)$/', $text, $matches)) {
				// This is an uppercased line. Use markdown
				$text = '## ' . ucfirst(strtolower($matches[1])) . ' ##';
			}
			if ($isPara) {
				$buffer[] = $text;			
			}
		}
		
		$story->setBody(implode("\n\n", $buffer));
		return $story;	
	}
	
	/**
	* Return all the story links found in the DOM
	**/
	public function getStories($dom) {
		echo "INFO: Looking for story links... ";
		$stories = array();
		$storyId = array();
		
		$anchors = $dom->find('a');
		foreach($anchors as $anchor) {
			$href = $anchor->href;
			if(preg_match($this->storyLinkPattern, $href, $matches)) {
				$story = new ReutersStory();
				$story->setTitle($anchor->plaintext);
				$story->setLink($this->normaliseLink($href));
				$story->setGuid($matches[2]);		
				$story->setCategory($matches[1]);
				
				if ($this->isStoryTitle($story->getTitle())) {
					if (empty($storyId[$story->getGuid()])) {
						$storyId[$story->getGuid()] = 1;
						$stories[] = $story;
					} else {
						//echo "WARN: Dupe: ", $story->title, "\n";
					}
				}
			} else {
				//echo "Skipping: [", $href, "]\n";
			}
		}
		
		echo count($stories), " stories\n";
		return $stories;	
	}
	
	protected function isStoryTitle($title) {
		if(empty($title)) {
			return false;
		} elseif ($title=='Full Article' || $title=='Full&nbsp;Article') {
			return false;
		}
		return true;
	}
	
	protected function isStoryLink($link) {
		return preg_match($this->storyLinkPattern, $link);
	}

	function getStoriesFromArchive($dom) {
		$stories = array();
		// Extract a date
		$header = $dom->find('div.contentBand h1', 0);
		//echo $header->plaintext, "\n";
		if (preg_match('/(\w+, \d+ \w+ \d+)/', $header->plaintext, $matches)) {
			$date = $matches[1];
			//echo "Found date: $date\n";
		}
		
		// Extract list of stories
		$headlines = $dom->find('div.primaryContent div.headlineMed');
		foreach ($headlines as $headline) {
			$story = (object) NULL;
			
			$link = $headline->find('a', 0);
			if (!empty($link)) {
				$story->title = $link->plaintext;
				$story->link  = strtolower($link->href);
			
				if (preg_match('/id(\w+\d{9,16})/', $link->href, $matches)) {
					$validId = true;
					$id = $matches[1];
					
					// See if the last bit is a proper date
					$idDate = substr($matches[1], -8);
					$ts = mktime(0, 0, 0, 
							substr($idDate,4,2),
							substr($idDate,6,2),
							substr($idDate,0,4)
						);
					$tsDate = date('Ymd', $ts);
					//echo $tsDate;

					if ($tsDate==$idDate) {
						$story->id   = $matches[1];
						$story->guid = substr($matches[1],0, strlen($matches[1])-8);
						//echo 'Valid date.';
					} else {
						$validId = false;
						echo "INFO: Skipping invalid ID: ", $matches[1], "\n";
					}

					if ($validId && preg_match(
							$this->timePattern, 
							$headline->plaintext, 
							$matches
					)) {
						$time = $date . ' ' . $matches[1];
						$timestamp = strtotime($time);
						//echo " [", $time, "] $timestamp";
						//echo ' ', date('c', $timestamp);
						$story->published = date('c', $timestamp);
					}
				} else {
					//echo "WARN: Can't extract id from {$link->href}\n";
				}
				
			}
			//print_r($story); break;
			if (!empty($story->id)) {
				//echo "* ", $story->id, ': ', $story->title, "\n";
				$stories[] = $story;
			}
		}
		
		return $stories;
	}
	
}



?>