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
    protected $result;

    function __construct(FormatterContract $formatter)
    {
        $this->path = new Path();
        $this->formatter = $formatter;
        $this->result = '';
    }

    public function crawlPath($path, $resultPath = '') {

        if (!file_exists($path)) {
            throw new InvalidPathException("Path: " . $path . " not found.");
        } else if (!is_readable($path)) {
            throw new UnreadablePathException("Unable to read the path", 1);
        }

        $result = [];
        $this->probePath($path, $result);

        $this->result = $this->formatter->format($result);

        // If the result path is provided, data will be saved as well
        if (!empty($resultPath)) {
            $this->path->setPath($resultPath);
            $this->path->saveContent($this->result);
        }

        return $this->result;
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

                $this->probePath($content, $parentItem[$path],  $fullPath . '/' .$content);
            };
        }
    }
}