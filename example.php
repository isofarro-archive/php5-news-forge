<?php

$cacheDir = 'cache/';

$htmlCacheDir = $cacheDir . 'html/';
$dataCacheDir = $cacheDir . 'data/uk-reuters-com/';

// Requires php5-http-client
require_once 'php5-http-client/HttpClient.php';
require_once 'php5-http-client/HttpRequest.php';
require_once 'php5-http-client/HttpResponse.php';

// Requires Simple HTML DOM
require_once 'simplehtmldom/simple_html_dom.php';

// And finally NewsForge itself.
require_once 'NewsForge.php';
require_once 'NewsForgeInterfaces.php';


$forge = new NewsForge();
$forge->setCacheDir($cacheDir); //TODO: Move to $htmlCacheDir
//print_r($forge);

$cache = new FileCache($dataCacheDir);


// Get all the stories listed on the Reuters UK homepage
//$stories = $forge->getStories('http://uk.reuters.com/');

// Get all the stories listed on an archive page
$stories = $forge->getStories(
	'http://uk.reuters.com/resources/archive/uk/20090124.html'
);

//print_r($stories);

if (true) {
	foreach ($stories as $story) {
		echo " * ", $story->getTitle(), "\n";
	}
} elseif(true) {
	$story = $stories[0];
	echo 'Getting story: ', $story->getTitle(), "\n";
	echo 'Getting: ', $story->getParseStoryLink(), "\n";
	$storyData = $forge->getStory($story);
	print_r($storyData);
}
?>
