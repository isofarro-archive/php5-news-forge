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
		'object' => 'NewsForgeObjectCache',
		'story'  => 'NewsForgeStoryCache',
		'json'   => 'NewsForgeJsonCache',
		'misc'   => 'NewsForgeGenericCache'
	);

	public function getRootDir() {
		return $this->rootDir;
	}
	
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

	public function isCached($type, $guid, $obj=false) {
		$filter   = $this->getCacheFilter($type);
		$filePath = $filter->getFilePath($guid, $obj);

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
		$val = file_put_contents($filePath, $ser);
		return ($val!==false)?true:false;
	}
	
	public function get($type, $guid, $obj=false) {
		$filter   = $this->getCacheFilter($type);
		$filePath = $filter->getFilePath($guid, $obj);

		if (file_exists($filePath)) {
			// Put some error checking in here
			$ser = file_get_contents($filePath);
			return $filter->unserialiseObject($ser);
		}
		return NULL;
	}

	public function delete($type, $guid, $obj=false) {
		$filter   = $this->getCacheFilter($type);
		$filePath = $filter->getFilePath($guid, $obj);

		if (file_exists($filePath)) {
			return unlink($filePath);
		}
		return false;
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
					echo "WARN: Cache directory $dir is not writeable.";
				}
			} else {
				echo "WARN: $dir is not a directory.";
			}		
		} else {
			echo "WARN: Cache directory $dir does not exist.";
		}
		return false;
	}
		
}


class NewsForgeGenericCache {
	protected $rootDir;

	protected $defaultExpiry = 30;
	protected $dir = 'misc/';
	protected $ext = '.data';
	
	// TODO: set get/set for $dir and $ext
	// Check that they are clean alphanumerics
	
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
		if ($domain === false) {
			$domainDir = $this->rootDir . $this->dir;
		} else {
			$domainDir = $this->rootDir . $this->dir . $domain . '/';
			if (!$this->initDomainDir($domainDir)) {
				return NULL;
			}
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
			if (!@mkdir($domainDir)) {
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

class NewsForgeObjectCache extends NewsForgeGenericCache {
	protected $dir = 'object/';
	protected $ext = '.obj';

	public function getFilePath($guid, $obj=false) {
		// The GUID is it. No domain name with a generic object cache
		// TODO: probably should strip nasty chars from guid.
		$key      = $guid;
		$filePath = $this->getFullPath(false) . $key . $this->ext;	
		return $filePath;
	}
			
	public function serialiseObject($obj) {
		return serialize($obj);
	}
	
	public function unserialiseObject($obj) {
		return unserialize($obj);
	}
}

class NewsForgeStoryCache extends NewsForgeObjectCache {
	protected $dir = 'story/';
	protected $ext = '.ser';
	
	public function getFilePath($guid, $obj=false) {
		// The GUID is going to be ineffectual.
		// The obj will either be the story object or the URL.
		$domain = '';
		
		if ($obj!==false) {
			if (is_string($obj)) {
				if (preg_match('/^[^.:\/]+(\.[^.]+)+$/', $obj)) {
					// The string matches a domain name
					$domain = $obj;				
				} else {
					// Might be a URL. Try to extract a domain
					$domain = $this->getDomain($obj);
				}
			} elseif (is_object($obj) && is_a($obj, 'NewsForgeStory')) {
				// We have a story object, so can use the original link
				$domain = $this->getDomain($obj->getLink());
			}
		} else {
			// So we only have a GUID.
			// Maybe it is a URL, or a domain based URI
			$domain = $this->getDomain($obj);
		}

		$key      = md5($guid);
		$filePath = $this->getFullPath($domain) . $key . $this->ext;
		return $filePath;
	}
}

class NewsForgeUrlCache extends NewsForgeGenericCache {
	protected $dir = 'url/';
	protected $ext = '.url';

	public function getFilePath($guid, $obj=false) {
		$domain   = $this->getDomain($guid);
		$key      = md5($guid);
		$filePath = $this->getFullPath($domain) . $key . $this->ext;	
		return $filePath;
	}
}

class NewsForgeXmlCache extends NewsForgeUrlCache {
	protected $dir = 'xml/';
	protected $ext = '.xml';
}

class NewsForgeHtmlCache extends NewsForgeUrlCache {
	protected $dir = 'html/';
	protected $ext = '.html';
}

class NewsForgeCalaisCache extends NewsForgeXmlCache {
	//protected $dir = 'xml/'; // By inheritance
	protected $ext = '.calais.xml/';
}

class NewsForgeJsonCache extends NewsForgeUrlCache {
	protected $dir = 'json/';
	protected $ext = '.json';
}



?>