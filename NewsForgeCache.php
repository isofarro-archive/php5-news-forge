<?php

class NewsForgeCache {
	protected $rootDir;
	protected $defaultCacheType = 'misc';
	protected $cacheFilters     = array();
	
	// Extension Cache filters
	protected $cacheTypes = array(
		'html'   => 'NewsForgeHtmlCache',
		'xml'    => 'NewsForgeXmlCache',
		'calais' => 'NewsForgeCalaisCache',
		'story'  => 'NewsForgeStoryCache',
		'json'   => 'NewsForgeJsonCache',
		'misc'   => 'NewsForgeGenericCache'
	);
	
	public function setRootDir($dir) {
		// Check the dir ends in a /
		if (substr($dir, -1)!=='/') {
			$dir .= '/';
		}
		if ($this->isCacheReadyDir($dir)) {
			$this->rootDir = $dir;
			// TODO: Go through each of the filters
			//       and initialise their directories
		}
	}

	public function isCached($type, $guid) {
		$filter   = $this->getCacheFilter($type);
		$filePath = $filter->getFilePath($guid);

		if (file_exists($filePath)) {
			// TODO: check staleness if there is a time parameter.
			return true;
		}
		return false;
	}	
	
	public function cache($type, $guid, $data) {
		$filter   = $this->getCacheFilter($type);
		$filePath = $filter->getFilePath($guid, $data);
		$ser      = $filter->serialiseObject($data);

		// TODO: put some error checking in here
		file_put_contents($filePath, $ser);
	}
	
	public function get($type, $guid) {
		$filter   = $this->getCacheFilter($type);
		$filePath = $filter->getFilePath($guid);

		if (file_exists($filePath)) {
			// Put some error checking in here
			$ser = file_get_contents($filePath);
			return $filter->unserialiseObject($ser);
		}
		return NULL;
	}


	
	
	
	protected function getCacheFilter($type) {
		$filter = NULL;
		if(empty($this->cacheTypes[$type])) {
			$type = $this->defaultCacheType;
		}
		if (empty($this->cacheFilters[$type])) {
			// Create a new filter
			if (!empty($this->cacheTypes[$type])) {
				$className = $this->cacheTypes[$type];
				if (class_exists($className)) {
					$cacheExt = new $className();
					if (is_a($cacheExt, 'NewsForgeGenericCache')) {
						$cacheExt->setRootDir($this->rootDir);
						$this->cacheFilters[$type] = $cacheExt;
						$filter = $cacheExt;
					} else {
						echo "ERROR: $className does not extend NewsForgeGenericCache\n";
					}
				} else {
					echo "ERROR: No cache class called $className found.\n";
				}
			} else {
				echo "ERROR: No cache type for $type defined\n";
			}
			
		} else {
			// Reuse the already created Filter
			$filter = $this->cacheFilters[$type];
		}
		return $filter;
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
		
}


class NewsForgeGenericCache {
	protected $rootDir;

	protected $defaultExpiry = 30;
	protected $dir = 'misc/';
	protected $ext = '.data';
	
	public function setRootDir($rootDir) {
		$this->rootDir = $rootDir;
		$this->initTypeDir();
	}	

	public function getFilePath($guid, $obj=false) {
		return $this->getFullPath() . $guid . $this->ext;	
	}
	
	public function serialiseObject($obj) {
		return $obj;
	}
	
	public function unserialiseObject($obj) {
		return $obj;
	}


	protected function getFullPath($domain) {
		$domainDir = $this->rootDir . $this->dir . $domain . '/';
		if (!$this->initDomainDir($domainDir)) {
			return NULL;
		}
		return $domainDir;
	}
	
	protected function initTypeDir() {
		$dirPath = $this->rootDir . $this->dir;
		if (!file_exists($dirPath)) {
			if (!mkdir($dirPath)) {
				echo "ERROR: Couldn't create $dirPath\n";
				return false;
			}				
		}
		return true;
	}

	protected function initDomainDir($domainDir) {
		if (!file_exists($domainDir)) {
			if (!mkdir($domainDir)) {
				echo "ERROR: Couldn't create $domainDir\n";
				return false;
			}				
		}
		return true;
	}

	protected function getDomain($url) {
		$segments = parse_url($url);
		return $segments['host'];
	}
}

class NewsForgeStoryCache extends NewsForgeGenericCache {
	protected $dir = 'story/';
	protected $ext = '.ser';
	
	public function getFilePath($guid, $obj=false) {
		// The GUID is going to be ineffectual.
		// The obj will either be the story object or the URL.
		$domain = '';
		
		if ($obj) {
			if (is_object($obj) && is_a($obj, 'NewsForgeStory')) {
				// We have a story object, so can use the original link
				$domain = $this->getDomain($obj->getLink());
			} elseif(is_string($obj)) {
				if (preg_match('/^[^.](\.$[^.])+$/', $obj)) {
					// The string matches a domain name
					$domain = $obj;				
				} else {
					// Might be a URL. Try to extract a domain
					$domain = $this->getDomain($obj);
				}
			}
		} else {
			// So we only have a GUID.
			// Maybe it is a URL, or a domain based URI
			$domain = $this->getDomain($obj);
		}

		$key    = md5($guid);
		$filePath = $this->getFullPath($domain) . $key . $this->ext;	
		return $filePath;
	}

	public function serialiseObject($obj) {
		return serialize($obj);
	}
	
	public function unserialiseObject($obj) {
		return unserialize($obj);
	}

}

class NewsForgeXmlCache extends NewsForgeGenericCache {
	protected $dir = 'xml/';
	protected $ext = '.xml';
	
	public function getFilePath($guid, $obj=false) {
		$domain = $this->getDomain($guid);
		$key    = md5($guid);
		$filePath = $this->getFullPath($domain) . $key . $this->ext;	
		return $filePath;
	}
}

class NewsForgeHtmlCache extends NewsForgeXmlCache {
	protected $dir = 'html/';
	protected $ext = '.html';
}

class NewsForgeCalaisCache extends NewsForgeXmlCache {
	//protected $dir = 'xml/'; // By inheritance
	protected $ext = '.calais.xml/';
}

class NewsForgeJsonCache extends NewsForgeXmlCache {
	protected $dir = 'json/';
	protected $ext = '.json';
}



?>