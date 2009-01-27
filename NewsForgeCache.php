<?php

class NewsForgeCache {
	protected $rootDir;
	protected $htmlDir    = 'html/';
	protected $dataDir    = 'data/';

	public function setRootDir($dir) {
		// TODO: Check the dir ends in a /
		if ($this->isCacheReadyDir($dir)) {
			$this->rootDir = $dir;
			$this->initRootDir();
		}
	}

	public function getUrl($url) {
		$filePath = $this->getUrlFilePath($url);

	}
	
	public function cacheUrl($url, $data) {
		$filePath = $this->getUrlFilePath($url);			
		return $this->save($filePath, $data);
	}




	protected function save($filePath, $data) {
		file_put_contents($filePath, $data);
	}
	
	protected function load($filePath) {
		if (file_exists($filePath)) {
			return file_get_contents($filePath);
		}
		return NULL;
	}
	
	protected function getUrlFilePath($url) {
		$domain = $this->getDomain($url);
		$key    = $this->getUrlKey($url);
		
		if ($this->initDomainDir($domain)) {
			$filePath = $this->rootDir . $this->htmlDir . $domain . '/' . 
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
		$dirList = array( $this->htmlDir, $this->dataDir );
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