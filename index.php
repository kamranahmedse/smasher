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
    $parentArray[$path]['-size'] = filesize($fullPath);
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

function createDirectories($outputDir, $content) {

    if (empty($content) || !is_array($content)) {
        return;
    }

    if (!is_dir($outputDir)) {
        mkdir($outputDir, 0777);
    }

    foreach ($content as $label => $detail) {

        // if it is a property
        if ( $label[0] === '-') {
            continue;
        }

        $toCreate = $outputDir . '/' . $label;

        if (!is_dir($toCreate)) {
            echo $toCreate . "<br>";
            $a = mkdir($toCreate, 0777, true);
        }

        createDirectories($toCreate, $detail);
    }

}

// $array = [];
// directoryToArray($path, $path, $array);
// $json = json_encode($array);
// file_put_contents('dir-contents.json', $json);

$json = file_get_contents('dir-contents.json');
$directories = json_decode($json, true);
createDirectories('output', $directories);

