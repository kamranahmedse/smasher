<?php

error_reporting(-1);
ini_set('display_errors', 'On');

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

function createItem($itemPath, $detail) {
    $old = umask(0);

    $type = $detail['-type'];

    if ($type === 'dir') {
        mkdir($itemPath, 0777, true);
    } else if ( $type === 'file') {
        $content = $detail['-content'];

        $handle = fopen($itemPath,"wb");
        fwrite($handle,$content);
        fclose($handle);

    } else if ($type === 'link') {
        $target = $detail['-destination'];
        $link = $itemPath;

        symlink($target, $link);
    }

    umask($old);
}

function directoryToArray( $path, $fullPath, &$parentArray ) {

    $parentArray[$path] = [];
 
    $parentArray[$path]['-name'] = $path;
    $parentArray[$path]['-type'] = $itemType = getPathType($fullPath);
    $parentArray[$path]['-path'] = $fullPath;
    $parentArray[$path]['-size'] = filesize($fullPath);
    $parentArray[$path]['-mode'] = substr(sprintf('%o', fileperms($fullPath)), -4);
    $parentArray[$path]['-owner'] = posix_getpwuid(fileowner($fullPath));
    $parentArray[$path]['-last_modified'] = date('Y-m-d H:i:s', filemtime($fullPath));
    $parentArray[$path]['-group'] = posix_getgrgid(filegroup($fullPath));

    if($itemType === 'link') {
        // Save the link detail
        $parentArray[$path]['-destination'] = realpath($fullPath);
    } else if ($itemType === 'file') {
        // If it was a file, put the contents
        $parentArray[$path]['-content'] = file_get_contents($fullPath);
    } else if ($itemType === 'dir') {
        // Recursively iterate the directory and find the inner contents
        $handle = opendir($fullPath);

        while($content = readdir($handle)) {
            if ( $content == '.' || $content == '..') {
                continue;
            }

            directoryToArray($content, $fullPath . '/' .$content, $parentArray[$path]);
        };
    }
}

function createDirectories($outputDir, $content) {

    if (empty($content) || !is_array($content)) {
        return;
    }

    if (!is_dir($outputDir)) {
        createItem($outputDir, ['-type' => 'dir']);
    }

    foreach ($content as $label => $detail) {

        // if it is a property
        if ( $label[0] === '-') {
            continue;
        }

        $toCreate = $outputDir . '/' . $label;

        if (!file_exists($toCreate)) {
            echo $toCreate . "<br>";
            createItem($toCreate, $detail);
        }

        if ($detail['-type'] == 'dir') {
            createDirectories($toCreate, $detail);
        }
    }
    
}

// $array = [];
// $path = 'sample-dir';
// directoryToArray($path, $path, $array);
// $json = json_encode($array);
// file_put_contents('dir-contents.json', $json);

// die($json);

$json = file_get_contents('dir-contents.json');
$directories = json_decode($json, true);
createDirectories('output', $directories);

