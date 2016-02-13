<?php


$path = 'sample-dir';

function getPathType($path) {
    if ( is_file($path)) {
        return 'file';
    } else if (is_link($path)) {
        return 'link';
    } else if (is_dir($path)) {
        return 'dir';
    }

    return "Unknown";
}

function directoryToArray( $path, $fullPath, &$parentArray ) {

    $parentArray[$path] = [];

    $parentArray[$path]['-type'] = getPathType($fullPath);
    $parentArray[$path]['-path'] = $fullPath;

    // If it was a file, put the contents
    if (is_file($fullPath)) {
        $parentArray[$path]['content'] = file_get_contents($fullPath);
    }

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