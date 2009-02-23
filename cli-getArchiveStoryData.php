<?php

// Default
$cacheDir = 'cache/';

// Override for DEV env:
include_once 'config.php';
include_once 'archiveQueue.php';


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

if (empty($archives)) {
	$archives = array(
		'20090120', '20090119', '20090118', '20090117',
		'20090116', '20090115', '20090114', '20090113'
	);
}

foreach ($archives as $archive) {
	$archiveUrl = 'http://uk.reuters.com/resources/archive/uk/' . $archive . '.html';
	$stories = $forge->getStories($archiveUrl);

	$total = count($stories);
	echo "$total stories from $archive.\n";
	echo "Pre-caching story html:\n";

	$count = $total;
	foreach ($stories as $story) {
		//$storyData = $forge->getStory($story);
		$storyData = $forge->getStoryHtml(
			$story->getParseStoryLink(),
			$story->getLink()
		);
		echo 	$count, ':',
			$story->getGuid(), ': ', 
			$story->getTitle(), ' (', 
			strlen($storyData), ")\n";
		$count--;
 
		//break;
		sleep(5); // Use when not HTML cached
	}
	echo "-------------------------------\n";
}


?>
