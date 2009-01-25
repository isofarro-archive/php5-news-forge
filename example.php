<?php

// Requires php5-http-client
require_once 'php5-http-client/HttpRequest.php';
require_once 'php5-http-client/HttpResponse.php';
require_once 'php5-http-client/HttpClient.php';

// Requires Simple HTML DOM
require_once 'simplehtmldom/simple_html_dom.php';

// And finally NewsForge itself.
require_once 'newsForge.php';


$forge = new NewsForge();



?>