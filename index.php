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

    $parentArray[$path]['-name'] = $path;
    $parentArray[$path]['-type'] = getPathType($fullPath);
    $parentArray[$path]['-path'] = $fullPath;
    $parentArray[$path]['-filesize'] = filesize($fullPath);
    $parentArray[$path]['-mode'] = substr(sprintf('%o', fileperms($fullPath)), -4);
    $parentArray[$path]['-owner'] = posix_getpwuid(fileowner($fullPath));
    $parentArray[$path]['-last_modified'] = date('Y-m-d H:i:s', filemtime($fullPath));
    $parentArray[$path]['-group'] = posix_getgrgid(filegroup($fullPath));

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