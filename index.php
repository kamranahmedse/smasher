<?php


$path = 'sample-dir/';


function directoryToArray( $path, &$parentArray ) {

    // If we have reached a file
    if (!is_dir($path)) {
        return [];
    }

    $parentArray[$path] = [];

    $handle = opendir($path);
    while($content = readdir($handle)) {


        if ( $content == '.' || $content == '..') {
            continue;
        }

        $parentArray[$path] = directoryToArray($content, $parentArray[$path]);
    };

    return $parentArray;
}

$array = [];
directoryToArray($path, $array);
echo "<pre>";
print_r($array);