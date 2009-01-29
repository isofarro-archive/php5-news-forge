<?php

require_once 'PHPUnit/Framework.php';
require_once '../NewsForgeCache.php';

class NewsForgeCacheTests extends PHPUnit_Framework_TestCase {
	protected $cacheRootDir = '/tmp/cache-test/';

	protected $htmlUrl  = 'http://www.example.com/index.html';
	protected $htmlBody = '<h1>This is a test html file</h1>';

	protected $xmlUrl  = 'http://www.example.com/index.xml';
	protected $xmlBody = '<xml>This is a test xml file</xml>';

	protected function setUp() {
		// Create a cache directory
		if (!mkdir($this->cacheRootDir)) {
			echo "ERROR: cannot create cache root directory ", 
				$this->cacheRootDir, "\n";
		}
	}

	protected function tearDown() {
		if (file_exists($this->cacheRootDir)) {
			$output = `rm -rf /tmp/cache-test/*`;
			//echo "INFO: $output\n";
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

	public function testRootDir() {
		$cache = new NewsForgeCache();
		
		$cache->setRootDir($this->cacheRootDir);
		$this->assertEquals($this->cacheRootDir, $cache->getRootDir());
		
		// Check it adds the trailing slash
		$cache->setRootDir('/tmp');
		$this->assertEquals('/tmp/', $cache->getRootDir());
		
		$cache->setRootDir($this->cacheRootDir);
		$this->assertEquals($this->cacheRootDir, $cache->getRootDir());
	}	

	// TODO: test caching without setting a root dir
	
	public function testHtmlCache() {
		$cache = new NewsForgeCache();
		$cache->setRootDir($this->cacheRootDir);

		$this->assertFalse($cache->isCached('html', $this->htmlUrl));
		
		$cache->cache('html', $this->htmlUrl, $this->htmlBody);
		$this->assertTrue($cache->isCached('html', $this->htmlUrl));
		
		$htmlBody = $cache->get('html', $this->htmlUrl);

		$cacheFilename = $this->cacheRootDir . 'html/www.example.com/' .
			md5($this->htmlUrl) . '.html';
		$this->assertTrue(file_exists($cacheFilename));
		
		$this->assertNotNull($htmlBody);
		$this->assertEquals($htmlBody, $this->htmlBody);
		
		$success = $cache->delete('html', $this->htmlUrl);
		$this->assertTrue($success);
		$this->assertFalse($cache->isCached('html', $this->htmlUrl));
		
		$success = $cache->delete('html', $this->htmlUrl);
		$this->assertFalse($success);
		$this->assertFalse($cache->isCached('html', $this->htmlUrl));
	}

	public function testXmlCache() {
		$cache = new NewsForgeCache();
		$cache->setRootDir($this->cacheRootDir);

		$this->assertFalse($cache->isCached('xml', $this->xmlUrl));
		
		$cache->cache('xml', $this->xmlUrl, $this->xmlBody);
		$this->assertTrue($cache->isCached('xml', $this->xmlUrl));
		
		$xmlBody = $cache->get('xml', $this->xmlUrl);

		$cacheFilename = $this->cacheRootDir . 'xml/www.example.com/' .
			md5($this->xmlUrl) . '.xml';
		$this->assertTrue(file_exists($cacheFilename));		
		
		$this->assertNotNull($xmlBody);
		$this->assertEquals($xmlBody, $this->xmlBody);
		
		$success = $cache->delete('xml', $this->xmlUrl);
		$this->assertTrue($success);
		$this->assertFalse($cache->isCached('xml', $this->xmlUrl));
		
		$success = $cache->delete('xml', $this->xmlUrl);
		$this->assertFalse($success);
		$this->assertFalse($cache->isCached('xml', $this->xmlUrl));
	}

}


?>