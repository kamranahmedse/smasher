<?php 

/**
* Spider
* 
* Responsible for crawling the paths and gathering information
*/
class Spider
{
    protected $path;
    protected $formatter;

    function __construct(FormatterContract $formatter)
    {
        $this->path = new Path();
        $this->formatter = $formatter;
    }

    public function crawl($path) {

        if (!file_exists($path)) {
            throw new InvalidPathException("Path: " . $this->path . " not found.");
        } else if (!is_readable($path)) {
            throw new UnreadablePathException("Unable to read the path", 1);
        }

        $result = [];
        $this->probePath($path, $result);

        return $this->formatter->format($result);
    }

    public function probePath($path, &$parentItem, $fullPath = '') {

        if (empty($fullPath)) {
            $fullPath = $path;
        }

        $this->path->setPath($fullPath);
        $parentItem[$path] = $this->path->getDetail();
        
        if ($this->path->getType() === 'dir') {
            // Recursively iterate the directory and find the inner contents
            $handle = opendir($fullPath);

            while($content = readdir($handle)) {
                if ( $content == '.' || $content == '..') {
                    continue;
                }

                $this->crawl($content, $parentItem[$path],  $fullPath . '/' .$content);
            };
        }
    }
}