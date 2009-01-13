<?php

require_once 'simplehtmldom/simple_html_dom.php';
require_once 'newsForge.php';

$cacheDir = '/home/user/data/news-forge/cache/';

// Offline HTML store
$htmlDir  = '/home/user/data/tagtimes/html-cache/uk-reuters-com/';


$forge = new NewsForge();
$forge->setCacheDir($cacheDir);

$dayIndex = file_get_contents($htmlDir . '20070101.html');

$stories = $forge->getStories('http://uk.reuters.com/', $dayIndex);

echo "Retrieved ", count($stories), " stories\n";

?>