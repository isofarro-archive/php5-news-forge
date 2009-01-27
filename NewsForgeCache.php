<?php

class NewsForgeCache {
	protected $rootDir;
	protected $htmlDir    = 'html/';
	protected $dataDir    = 'data/';
	protected $xmlDir     = 'xml/';

	protected $defaultExpiry = 30;

	public function setRootDir($dir) {
		// Check the dir ends in a /
		if (substr($dir, -1)!=='/') {
			$dir .= '/';
		}
		if ($this->isCacheReadyDir($dir)) {
			$this->rootDir = $dir;
			$this->initRootDir();
		}
	}
	
	public function setDefaultExpiry($expiry) {
		$this->defaultExpiry = $expiry;
	}

	public function isHtmlCached($url, $time=false) {
		$filePath = $this->getUrlFilePath($url, 'html');
		return $this->isCached($filePath);
	}

	public function getHtml($url) {
		$filePath = $this->getUrlFilePath($url, 'html');
		return $this->load($filePath);
	}
	
	public function cacheHtml($url, $data) {
		$filePath = $this->getUrlFilePath($url, 'html');
		return $this->save($filePath, $data);
	}





	protected function isCached($filePath, $time=false) {
		if (file_exists($filePath)) {
			// TODO: check staleness if there is a time parameter.
			return true;
		}
		return false;
	}	
	
	protected function save($filePath, $data) {
		// TODO: put some error checking in here
		file_put_contents($filePath, $data);
	}
	
	protected function load($filePath) {
		if (file_exists($filePath)) {
			// Put some error checking in here
			return file_get_contents($filePath);
		}
		return NULL;
	}
	
	
	protected function getUrlFilePath($url, $ext) {
		$domain = $this->getDomain($url);
		$key    = $this->getUrlKey($url, $ext);
		
		$extPath = '';
		switch($ext) {
			case 'html':
				$extPath = $this->htmlDir;
				break;
			case 'xml':
				$extPath = $this->xmlDir;
				break;
		}
		
		if ($this->initDomainDir($domain)) {
			$filePath = $this->rootDir . $extPath . $domain . '/' . 
				$key . '.html';	
		}
			
		return $filePath;
	}

	protected function getDomain($url) {
		$segments = parse_url($url);
		return $segments['host'];
	}
	
	protected function getUrlKey($url) {
		return md5($url);
	}
	
	protected function splitUrl($url) {
		return parse_url($url);
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
	
	protected function initRootDir() {
		$dirList = array( $this->htmlDir, $this->dataDir, $this->xmlDir );
		foreach($dirList as $dir) {
			$dirPath = $this->rootDir . $dir;
			if (!file_exists($dirPath)) {
				if (!mkdir($dirPath)) {
					echo "ERROR: Couldn't create $dirPath\n";
				}				
			}
		}
	}
	
	protected function initDomainDir($domain) {
		$dirList = array( $this->htmlDir, $this->dataDir );
		foreach($dirList as $dir) {
			$dirPath = $this->rootDir . $dir . $domain . '/';
			if (!file_exists($dirPath)) {
				if (!mkdir($dirPath)) {
					echo "ERROR: Couldn't create $dirPath\n";
					return false;
				}				
			}
		}
		return true;
	}
	
}


?>