<?php
/**
	A generic API wrapper
	
**/
class NewsForge {
	protected $cache;
	//protected $spider;
	protected $httpClient;
	
	protected $forges = array(
		'uk.reuters.com' => 'UkReutersForge'
	);
	
	public function setCacheDir($dir) {
		$this->cache = new FileCache($dir);
	}

	/**
	*	Takes any URL and returns a list of story links on that page
	**/
	public function getStories($url) {
		$domain  = $this->getDomain($url);
		$forge   = $this->getForge($domain);
		$html    = $this->getUrl($url);
		$this->log('INFO', 'HTML returned (' . strlen($html) . ") bytes");
		$dom     = $this->getDom($html);
		$stories = $forge->getStories($dom);
		
		return $stories;
	}	

	
	protected function getDom($html) {
		return str_get_html($html);
	}

	protected function getUrl($url) {
		if ($this->cache->isCachedUrl($url)) {
			return $this->cache->getUrl($url);
		} else {
			$http = $this->getHttpClient();

			$request = new HttpRequest();
			$request->setMethod('GET');
			$request->setUrl($url);
		
			$response = $http->doRequest($request);
			//print_r($response);

			// Cache the response			
			if ($response->getBody()) {
				$this->cache->cacheUrl($url, $response->getBody());
			}
		}
		return $response->getBody();
	}

	/**
	*	Extracts the domain from the URL
	**/
	protected function getDomain($url) {
		//echo "Domain find: $url\n";
		// Extract the domain name from the URL
		if (preg_match('/\:\/\/([^\/:]+)\//', $url, $matches)) {
			//echo "Domain extracted: ", $matches[1], "\n";
			return strtolower($matches[1]);
		} else {
			$this->log('WARN', "Can't find domain in $url.");
		}
		return NULL;
	}
	
	/**
	*	Gets the Forge class for a particular domain
	**/
	protected function getForge($domain) {
		if (!empty($this->forges[$domain])) {
			$className = $this->forges[$domain];
			if (class_exists($className)) {
				$forge = new $className();
				if (is_a($forge, 'NewsForgeApi')) {
					return $forge;
				} else {
					$this->log('WARN', 
						"$className doesn't implement NewsForgeApi."); 
				}
			} else {
				$this->log('WARN', "NewsForge class $className doesn't exist");
			}
		} else {
			$this->log('WARN', "No NewsForge API specified for $domain");
		}
	}
	
	protected function getHttpClient() {
		if (!$this->httpClient) {
			$this->httpClient = new HttpClient();
		}
		return $this->httpClient;
	}
	
	public function log($level, $msg) {
		echo $level, ': ', $msg, "\n";
	}

}


class FileCache {

	public function __construct($dir=false) {
		if ($dir && $this->isCacheReadyDir($dir)) {
			$this->cacheDir = $dir;
		}
	}

	public function cacheUrl($url, $body) {
		$key = $this->hashUrl($url);
		return $this->cache($key, $body);
	}
	
	public function getUrl($url) {
		$key = $this->hashUrl($url);
		return $this->get($key);
	}
	
	public function isCachedUrl($url) {
		$key = $this->hashUrl($url);
		return $this->isCached($key);
	}
	
	public function cache($key, $body) {
		$filePath = $this->cacheDir . $key;
		file_put_contents($filePath, $body);
		echo "INFO: Cached $key: (", strlen($body), ")\n";
		return true;
	}
	
	public function get($key) {
		$filePath = $this->cacheDir . $key;
		if (file_exists($filePath)) {
			echo "INFO: Cache hit $key\n";
			return file_get_contents($filePath);
		} else {
			echo "WARN: $filePath not a cached file.\n";
		}
		return NULL;
	}
	
	public function isCached($key) {
		$filePath = $this->cacheDir . $key;
		//echo "Looking for $filePath\n";
		return file_exists($filePath);
	}

	protected function hashUrl($url) {
		return md5($url) . '.html';
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