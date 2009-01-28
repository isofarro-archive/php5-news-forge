<?php

require_once 'NewsForgeCache.php';

$rootCacheDir = '/home/user/data/news-forge/cache/';
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

if ($cache->isHtmlCached($url)) {
	echo "INFO: Caching URL\n";
	$cache->cacheHtml($url, $body);
}

echo "INFO: Getting cached URL\n";
$cachedBody = $cache->getHtml($url);

if ($body===$cachedBody) {
	echo "INFO: Cached entry match\n";
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

if ($cache->isXmlCached($xmlUrl)) {
	echo "INFO: Caching XML URL\n";
	$cache->cacheXml($xmlUrl, $xmlBody);
}

echo "INFO: Getting cached XML URL\n";
$cachedXmlBody = $cache->getXml($xmlUrl);

if ($xmlBody===$cachedXmlBody) {
	echo "INFO: Cached entry match\n";
}


?>