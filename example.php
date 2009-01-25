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


$forge = new NewsForge();
$forge->setCacheDir($cacheDir);
//print_r($forge);

// Get all the stories listed on the Reuters UK homepage
$stories = $forge->getStories('http://uk.reuters.com/');
//print_r($stories);

foreach ($stories as $story) {
	echo " * ", $story->title, "\n";
}

//$story = $stories[0];
//echo 'Getting story: ', $story->title, "\n", $story->href, "\n";
//$storyData = $forge->getStory($story->href);

?>