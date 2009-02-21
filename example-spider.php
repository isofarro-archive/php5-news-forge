<?php

$cacheDir = 'cache/';

// Override for DEV env:
$devCacheDir = '/media/disk/data-cache/newsforge/';
if (file_exists($devCacheDir) && is_dir($devCacheDir)) {
	echo "Cache using $devCacheDir\n";
	$cacheDir = $devCacheDir;
}

// Requires php5-http-client
require_once 'php5-http-client/HttpClient.php';
require_once 'php5-http-client/HttpRequest.php';
require_once 'php5-http-client/HttpResponse.php';

// Requires Simple HTML DOM
require_once 'simplehtmldom/simple_html_dom.php';

// And finally NewsForge itself.
require_once 'NewsForge.php';
require_once 'NewsForgeInterfaces.php';
require_once 'NewsForgeCache.php';

$forge = new NewsForge();
$forge->setCacheDir($cacheDir); 
//print_r($forge);

$forge->spider('http://uk.reuters.com/');


?>