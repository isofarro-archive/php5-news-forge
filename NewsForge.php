<?php

/**
*	NewsForge - Forging an API on existing news sites
*	
**/
class NewsForge {
	protected $cache;
	//protected $spider;
	protected $httpClient;
	
	protected $forges = array(
		'uk.reuters.com' => 'UkReutersForge'
	);
	
	public function setCacheDir($dir) {
		$this->cache = new NewsForgeCache();
		$this->cache->setRootDir($dir);
	}
	
	public function getStory($story) {
		// TODO: use a decorator pattern to initialise
		// request object on a domain by domain basis	
	
		$link      = $story->getLink();
		$prefLink  = $story->getParseStoryLink();
		$domain    = $this->getDomain($prefLink);
		$forge     = $this->getForge($domain);
		
		//$html      = $this->getUrl($prefLink, $link);
		$html      = $this->getStoryHtml($prefLink, $link);

		//$this->log('INFO', 'HTML returned (' . strlen($html) . ") bytes");
		$forge->setUrl($prefLink);
		$dom       = $this->getDom($html);
		$storyData = $forge->getStory($dom, $story);
		$dom->clear();

		return $storyData;
	}

	public function getStoryHtml($url, $refUrl) {
		return $this->getUrl($url, $refUrl);
	}

	/**
	*	Takes any URL and returns a list of story links on that page
	**/
	public function getStories($url) {
		$domain  = $this->getDomain($url);
		$forge   = $this->getForge($domain);
		$html    = $this->getUrl($url);

		$this->log('INFO', 'HTML returned (' . strlen($html) . ") bytes");
		$forge->setUrl($url);
		$dom     = $this->getDom($html);
		$stories = $forge->getStories($dom);
		
		return $stories;
	}	

	
	protected function getDom($html) {
		return str_get_html($html);
	}

	protected function getUrl($url, $referrer=NULL) {
		if ($this->cache->isCached('html', $url)) {
			return $this->cache->get('html', $url);
		} else {
			$request = new HttpRequest();
			$request->setMethod('GET');
			$request->setUrl($url);
			
			if ($referrer && $url!=$referrer) {
				// Add an HTTP Referer header
				//echo "INFO: adding Referer header\n";
				$request->addHeader('Referer', $referrer);		
			}

			//print_r($request);
			$response = $this->getResponse($request);
			//print_r($response);

			// Cache the response			
			if ($response->getBody()) {
				$this->cache->cache('html', $url, $response->getBody());
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
					$forge->setDomain($domain);
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
	
	protected function getResponse($request) {
		$http = $this->getHttpClient();
		return $http->doRequest($request);
	}
	
	public function log($level, $msg) {
		echo $level, ': ', $msg, "\n";
	}

}


/**
*	NewsForgeStory an object encapsulating story data
**/
class NewsForgeStory {
	protected $title;
	protected $link;
	protected $guid;
	protected $published;
	protected $category;
	protected $author;
	
	protected $body;
	
	public function getTitle() {
		return $this->title;	
	}
	
	public function setTitle($title) {
		$this->title = $this->normaliseTitle($title);
	}
	
	public function getLink() {
		return $this->link;
	}
	
	public function setLink($link) {
		$this->link = $link;
	}
	
	public function getGuid() {
		return $this->guid;
	}
	
	public function setGuid($guid) {
		$this->guid = $guid;
	}
	
	public function getPublished() {
		return $this->published;
	}
	
	public function setPublished($published) {
		$this->published = $published;
	}
	
	public function getCategory() {
		return $this->category;
	}
	
	public function setCategory($category) {
		$this->category = $category;
	}
	
	public function getAuthor() {
		return $this->author;
	}
	
	public function setAuthor($author) {
		$this->author = $author;
	}
	
	public function getBody() {
		return $this->body;
	}
	
	public function setBody($body) {
		$this->body = $body;
	}
	
	
	// Helper methods
	public function getFullStoryLink() {
		return $this->link;
	}

	public function getPrintStoryLink() {
		return $this->link;
	}
	
	public function getParseStoryLink() {
		return $this->link;
	}
	
	public function normaliseTitle($title) {
		return $title;
	}

	public function getCacheKey() {
		// TODO check GUID is filename happy
		return $this->guid . '.ser';
	}

}


?>
