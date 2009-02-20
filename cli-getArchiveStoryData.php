<?php

$cacheDir = 'cache/';

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


// Get all the stories listed on the Reuters UK homepage
//$stories = $forge->getStories('http://uk.reuters.com/');

// Get all the stories listed on an archive page
$stories = $forge->getStories(
	'http://uk.reuters.com/resources/archive/uk/20090124.html'
);


if (false) {
	foreach ($stories as $story) {
		//echo " * ", $story->getTitle(), "\n";
		if (strlen($story->getGuid()) < 16) {
			echo $story->getGuid(), ': ', substr($story->getTitle(),0, 40), "\n";
			//echo ' * ', $story->getLink(), "\n";
		}
	}
}


// Cache each story for further processing
foreach ($stories as $story) {
	$storyData = $forge->getStory($story);
	
	if (!empty($storyData)) {
		echo $story->getGuid(), ': ', 
			$story->getTitle(), ' (', 
			strlen($story->getBody()), ")\n";
	}
	//break;
	sleep(2); // Use when not HTML cached
}


?>
