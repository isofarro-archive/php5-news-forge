<?php

require_once 'NewsForgeCache.php';

$rootCacheDir = '/home/user/data/news-forge/cache-test/';
$cache = new NewsForgeCache();
$cache->setRootDir($rootCacheDir);
//print_r($cache);


$url = 'http://www.example.com/helloWorld.html';
$body = <<<HTML
<html>
	<head>
		<title>Hello World</title>
	</head>
	<body>
		<h1>Hello World</h1>	
	</body>
</html>
HTML;

if (!$cache->isCached('html', $url)) {
	echo "INFO: Caching HTML URL\n";
	$cache->cache('html', $url, $body);
}

echo "INFO: Getting cached HTML URL\n";
$cachedBody = $cache->get('html', $url);

if ($body===$cachedBody) {
	echo "INFO: Cached HTML entry match\n";
}


$xmlUrl  = 'http://www.example.com/index.rss';
$xmlBody = <<<XML
<rss>
	<title>An RSS Feed</title>
	<channel>
		<item>
			<title>An entry title</title>		
		</item>	
	</channel>	
</rss>
XML;

if (!$cache->isCached('xml', $xmlUrl)) {
	echo "INFO: Caching XML URL\n";
	$cache->cache('xml', $xmlUrl, $xmlBody);
}

echo "INFO: Getting cached XML URL\n";
$cachedXmlBody = $cache->get('xml', $xmlUrl);

if ($xmlBody===$cachedXmlBody) {
	echo "INFO: Cached XML entry match\n";
}


?>