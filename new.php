<?php

error_reporting(-1);
ini_set('display_errors', 'On');

require_once 'src/Path.php';
require_once 'src/Spider.php';


$spider = new Spider();
$spider->crawl('sample-dir');

echo "<pre>";
print_r($data);

