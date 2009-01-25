<?php



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