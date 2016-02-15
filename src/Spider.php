<?php 

/**
* Spider
* 
* Responsible for crawling the paths and gathering information
*/
class Spider
{
    protected $path;

    function __construct()
    {
        $this->path = new Path();
    }

    public function crawl($path, $fullPath, &$parentItem) {

        $this->path->setPath($fullPath);
        $parentItem[$path] = $this->path->getDetail();
        
        if ($this->path->getType() === 'dir') {
            // Recursively iterate the directory and find the inner contents
            $handle = opendir($fullPath);

            while($content = readdir($handle)) {
                if ( $content == '.' || $content == '..') {
                    continue;
                }

                directoryToArray($content, $fullPath . '/' .$content, $parentItem[$path]);
            };
        }
    }
}