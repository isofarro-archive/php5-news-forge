<?php

require_once 'PHPUnit/Framework.php';
require_once '../NewsForge.php';
require_once '../NewsForgeCache.php';

class NewsForgeGenericCacheTests extends PHPUnit_Framework_TestCase {
	protected $cacheRootDir = '/tmp/cache-type-test';

	public function setUp() {
		// Create a cache directory
		if (!@mkdir($this->cacheRootDir)) {
			echo "ERROR: cannot create cache root directory ", 
				$this->cacheRootDir, "\n";
		}
	}
	
	public function tearDown() {
		if (file_exists($this->cacheRootDir)) {
			$output = `rm -rf /tmp/cache-temp-test/*`; // */
			echo "INFO: $output\n";
			@rmdir($this->cacheRootDir);
		}
	}

	public function testRootDir() {
		$typeDir = 'misc';

		$cacheType = new NewsForgeGenericCache();
		$cacheType->setRootDir($this->cacheRootDir);

		$this->assertTrue(file_exists($this->cacheRootDir));		
		$this->assertTrue(is_dir($this->cacheRootDir));		
		$this->assertTrue(is_writeable($this->cacheRootDir));		
		$this->assertTrue(file_exists($this->cacheRootDir . $typeDir));		
		$this->assertTrue(is_dir($this->cacheRootDir . $typeDir));		
		$this->assertTrue(is_writeable($this->cacheRootDir . $typeDir));

	}

}

?>