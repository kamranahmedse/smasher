<?php namespace KamranAhmed\Smasher;

use KamranAhmed\Smasher\Contracts\ResponseContract;
use KamranAhmed\Smasher\Exceptions\InvalidContentException;
use KamranAhmed\Smasher\Exceptions\NoContentException;

/**
 * Spider
 *
 * Responsible for crawling the paths and gathering information
 */
class Scanner
{
    /**
     * @var \KamranAhmed\Smasher\Path The path to operate on
     */
    protected $path;

    /**
     * @var \KamranAhmed\Smasher\Contracts\ResponseContract Format for the response
     */
    protected $response;

    /**
     * @var string Encoded response which will be returned
     */
    protected $result;

    /**
     * Scanner constructor.
     *
     * @param \KamranAhmed\Smasher\Contracts\ResponseContract $response
     */
    public function __construct(ResponseContract $response)
    {
        $this->path     = new Path();
        $this->response = $response;
        $this->result   = '';
    }

    /**
     * Scans the provided path and returns the encoded response. Also if
     * resultPath is provided then the response will be stored in the resultPath
     *
     * @param        $path          The path to scan
     * @param string $resultPath    The path at which the resultant file will be created
     *
     * @throws \KamranAhmed\Smasher\Exceptions\InvalidPathException
     * @throws \KamranAhmed\Smasher\Exceptions\UnreadablePathException
     *
     * @return string
     */
    public function scan($path, $resultPath = '')
    {
        $this->path->setPath($path);
        $this->path->validate();

        $result = [];
        $this->probe($path, $result);

        $this->result = $this->response->encode($result);

        // If the result path is provided, data will be saved as well
        if (!empty($resultPath)) {
            $this->path->setPath($resultPath);
            $this->path->saveFileContent($this->result);
        }

        return $this->result;
    }

    /**
     * Probes the path and keeps populating the array with the results
     *
     * @param        $path
     * @param        $parentItem
     * @param string $fullPath
     */
    protected function probe($path, &$parentItem, $fullPath = '')
    {
        if (empty($fullPath)) {
            $fullPath = $path;
        }

        $pathParts = explode('/', $path);
        $path      = $pathParts[count($pathParts) - 1];

        $this->path->setPath($fullPath);
        $parentItem[$path] = $this->path->getDetail();

        if ($this->path->getType() === 'dir') {
            // Recursively iterate the directory and find the inner contents
            $handle = opendir($fullPath);

            while ($content = readdir($handle)) {
                if ($content == '.' || $content == '..') {
                    continue;
                }

                $this->probe($content, $parentItem[$path], $fullPath . '/' . $content);
            };
        }
    }

    /**
     * Uses the source file to populate the path provided
     *
     * @param $outputDir    The directory at which the content from file is to be populated
     * @param $sourceFile   The file to use for populating
     */
    public function populate($outputDir, $sourceFile)
    {
        $this->populatePath($outputDir, $sourceFile);
    }

    /**
     * Uses the smashed/response file to recursively populate the path
     *
     * @param       $outputDir   Where to populate the output
     * @param       $sourceFile  Path to the source file
     * @param array $content     The array to use instead of the source file
     * @param bool  $isRecursive Whether it is the recursive call
     *
     * @throws \KamranAhmed\Smasher\Exceptions\InvalidContentException
     * @throws \KamranAhmed\Smasher\Exceptions\NoContentException
     */
    private function populatePath($outputDir, $sourceFile, $content = [], $isRecursive = false)
    {
        if ($isRecursive === false) {
            $content = $this->getScannedContent($sourceFile);
        }

        if (!is_dir($outputDir)) {
            $this->path->setPath($outputDir);
            $this->path->createItem();
        }

        foreach ($content as $label => $detail) {

            // if it is a property
            if ($label[0] === '@') {
                continue;
            }

            $toCreate = $outputDir . '/' . $label;

            if (!file_exists($toCreate)) {
                $this->path->setPath($toCreate);
                $this->path->createItem($detail);
            }

            if (empty($detail['@type']) || ($detail['@type'] == 'dir')) {
                $this->populatePath($toCreate, '', $detail, true);
            }
        }
    }

    /**
     * Gets the scanned content from passed file
     *
     * @param $path
     *
     * @throws \KamranAhmed\Smasher\Exceptions\InvalidContentException
     * @throws \KamranAhmed\Smasher\Exceptions\NoContentException
     * @throws \KamranAhmed\Smasher\Exceptions\UnreadablePathException
     *
     * @return array
     */
    protected function getScannedContent($path)
    {
        $this->path->setPath($path);
        $this->path->validate();

        $content = $this->path->getFileContent();

        if (empty($content)) {
            throw new NoContentException("The file ' . $path . ' has no content", 1);
        }

        $result = $this->response->decode($content);

        if (!is_array($result)) {
            throw new InvalidContentException("The content in file " . $path . " could not be processed", 1);
        }

        return $result;
    }
}
