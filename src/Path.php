<?php namespace KamranAhmed\SquashDir;

use KamranAhmed\SquashDir\Exceptions\InvalidPathException;
use KamranAhmed\SquashDir\Exceptions\UnreadablePathException;

/**
 * Path
 *
 * Responsible for managing everything related to Paths
 */
class Path
{
    private $path;

    function __construct($path = '')
    {
        $this->path = $path;
    }

    public function validate() {
        if (!file_exists($this->path)) {
            throw new InvalidPathException("Path: " . $this->path . " not found.");
        } else if (!is_readable($this->path)) {
            throw new UnreadablePathException("Unable to read the path", 1);
        }
    }

    public function setPath($path) {
        $this->path = $path;
    }

    public function getMode() {
        return substr(sprintf('%o', fileperms($this->path)), -4);
    }

    public function getOwner() {
        return posix_getpwuid(fileowner($this->path));
    }

    public function getLastModified() {
        return date('Y-m-d H:i:s', filemtime($this->path));
    }

    public function getGroup() {
        return posix_getgrgid(filegroup($this->path));
    }

    public function getRealPath() {
        return realpath($this->path);
    }

    public function getFileContent() {
        return file_get_contents($this->path);
    }

    public function saveContent($content) {
        return file_put_contents($this->path, $content);
    }

    public function getPath() {
        return $this->path;
    }

    public function getType() {

        if ( is_file($this->path)) {
            return 'file';
        } else if (is_link($this->path)) {
            return 'link';
        } else if (is_dir($this->path)) {
            return 'dir';
        }

        return "Unknown";
    }

    public function getSize() {
        return filesize($this->path);
    }

    public function getName() {
        $parts = explode('/', $this->path);
        return array_pop($parts);
    }

    public function createItem($detail =  []) {

        // Default options
        $defaults = ['@type' => 'dir'];
        $detail = array_merge($defaults, $detail);

        $old = umask(0);

        $type = $detail['@type'];

        if ($type === 'dir') {
            mkdir($this->path, 0777, true);
        } else if ( $type === 'file') {
            $content = $detail['@content'];

            $handle = fopen($this->path,"wb");
            fwrite($handle,$content);
            fclose($handle);

        } else if ($type === 'link') {
            $target = $detail['@destination'];
            $link = $this->path;

            symlink($target, $link);
        }

        umask($old);
    } 

    public function getDetail() {

        $pathDetail  = [];

        $pathDetail['@name'] = $this->getName();
        $pathDetail['@path'] = $this->path;
        $pathDetail['@type'] = $this->getType();
        $pathDetail['@size'] = $this->getSize();
        $pathDetail['@mode'] = $this->getMode();
        $pathDetail['@owner'] = $this->getOwner();
        $pathDetail['@last_modified'] = $this->getLastModified();
        $pathDetail['@group'] = $this->getGroup();

        if($pathDetail['@type'] === 'link') {
            // Save the destination of this symlink
            $pathDetail['@destination'] = $this->getRealPath();
        } else if ($pathDetail['@type'] === 'file') {
            // If it was a file, put the contents
            $pathDetail['@content'] = $this->getFileContent();
        }

        return $pathDetail;
    }
}