<?php


$path = 'sample-dir/';


function directoryToArray( $path, $fullPath, &$parentArray ) {

    // If we have reached a file
    if (is_file($fullPath)) {
        return [];
    }

    $parentArray[$path] = [];
    $handle = opendir($fullPath);

    while($content = readdir($handle)) {

        if ( $content == '.' || $content == '..') {
            continue;
        }

        directoryToArray($content, $fullPath . '/' .$content, $parentArray[$path]);
    };
}

$array = [];
directoryToArray($path, $path, $array);
echo "<pre>";
print_r($array);