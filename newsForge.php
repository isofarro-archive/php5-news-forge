<?php

interface NewsParserInterface {
	public function getStories($dom);
	
}

class UkReutersParser implements NewsParserInterface {
	protected $timePattern = '/(\d{2}:\d{2} \w{2} \w{3})/';

	function getStories($dom) {
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


/**
	A generic news crawler
	
**/
class NewsForge {
	protected $cache;
	protected $spider;
	
	public function setCacheDir($dir) {
		$this->cache = new FileCache($dir);
	}
	
	public function getStories($url, $html=false) {
		
		if ($html) {
			$dom = str_get_html($html);
		} else {
			$dom = file_get_html($url);
		}
		
		if (!empty($dom)) {
			$parser  = $this->getParser($url);
			$stories = $parser->getStories($dom);
		} else {
			$this->logError('ERROR', "Couldn't parse DOM for $url.");
		}
		return $stories;
	}
	
	public function getParser($url) {
		$domain = $this->getDomain($url);
		$parser = NULL;
		
		switch($domain) {
			case 'uk.reuters.com':
				$parser = new UkReutersParser();
				break;
			default:
				$this->logError('WARN', "No parser for domain $domain.");
				break;
		}

		return $parser;
	}

	public function getDomain($url) {
		//echo "Domain find: $url\n";
		// Extract the domain name from the URL
		if (preg_match('/\:\/\/([^\/]+)\//', $url, $matches)) {
			//echo "Domain extracted: ", $matches[1], "\n";
			return $matches[1];
		} else {
			$this->logError('WARN', "Can't find domain in $url.\n");
		}
		return NULL;
	}
	
	public function getHtml($url) {
		if (!$this->spider) {
			$this->initSpider();
		}
		$this->spider->getPage($url);
	}

	public function initSpider() {
		$this->spider = new WebSpider();
	}

	public function logError($level, $msg) {
		echo $level, ': ', $msg, "\n";
	}
	
	private function initCacheDir($dir) {
		$this->cache = new FileCache($dir);
	}
}

/**
 Wrapper class around HTTP Client methods. Should support:
 * Curl
 * Socket connections
 * file_get_contents
**/
class WebSpider {

	public function __construct() {
		// TODO: Find the most appropriate HTTP Client to use here
	}

	/** Returns just the HTTP Body without any headers **/
	public function getPage($url) {
		$response = $this->getUrl($url);
		// TODO: return just the HTTP Body
	}
}

class FileCache {

	public function __construct($dir=false) {
		if ($dir && $this->isCacheReadyDir($dir)) {
			$this->cacheDir = $dir;
		}
	}
	
	protected function isCacheReadyDir($dir) {
		if (file_exists($dir)) {
			if (is_dir($dir)) {
				if (is_writeable($dir)) {
					return true;
				} else {
					$this->logError('WARN', "Cache directory $dir is not writeable.");
				}
			} else {
				$this->logError('WARN', "$dir is not a directory.");
			}		
		} else {
			$this->logError('WARN', "Cache directory $dir does not exist.");
		}
		return false;
	}

	public function logError($level, $msg) {
		echo $level, ': ', $msg, "\n";
	}
}

?>