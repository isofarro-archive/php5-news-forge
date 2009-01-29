<?php

require_once 'PHPUnit/Framework.php';
require_once '../NewsForgeCache.php';

class NewsForgeCacheTests extends PHPUnit_Framework_TestCase {
	protected $cacheRootDir = '/tmp/cache-test/';

	protected function setUp() {
		// Create a cache directory
		if (!mkdir($this->cacheRootDir)) {
			echo "ERROR: cannot create cache root directory ", 
				$this->cacheRootDir, "\n";
		}
	}

	protected function tearDown() {
		if (file_exists($this->cacheRootDir)) {
			rmdir($this->cacheRootDir);
		}
	}

	public function testInit() {
		$cache = new NewsForgeCache();
		$this->assertNotNull($cache);

		// Make sure our cache root dir is writeable
		$this->assertTrue(file_exists($this->cacheRootDir));
		$this->assertTrue(is_dir($this->cacheRootDir));
		$this->assertTrue(is_writeable($this->cacheRootDir));

		$cache->setRootDir($this->cacheRootDir);
		
		$this->assertNotNull($cache);
	}
	
	

}


?>