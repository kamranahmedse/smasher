<?php

namespace KamranAhmed\Smasher;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;

class ScannerTest extends \PHPUnit_Framework_TestCase
{
    private $sampleDirPath;
    private $invalidDirPath;
    private $outputJsonPath;
    private $basePathToPopulate;

    private $populatedDir;
    private $populatedFile;

    private $invalidScanSample;
    private $emptyScanSample;
    private $sampleJson;

    public function setUp()
    {
        $currentDir = __DIR__;

        $this->sampleDirPath      = $currentDir . '/data/sample-path';
        $this->invalidDirPath     = $currentDir . '/invalid/path/that/does/not/exist';
        $this->outputJsonPath     = $currentDir . '/data/output/sample-path.json';
        $this->basePathToPopulate = $currentDir . '/data/output/';

        $this->populatedDir  = $currentDir . '/data/output/sample-path';
        $this->populatedFile = $currentDir . '/data/output/sample-path/child-item/grand-child/child-file.md';

        $this->invalidScanSample = $currentDir . '/data/scanned-samples/invalid-scan.md';
        $this->emptyScanSample   = $currentDir . '/data/scanned-samples/empty-scan.json';
        $this->sampleJson        = $currentDir . '/data/scanned-samples/scanned-json.json';
    }

    public function testCanScanPathAndGetResult()
    {
        $scanner    = new Scanner(new JsonResponse());
        $scanResult = $scanner->scan($this->sampleDirPath);

        $this->assertTrue($this->isValidJson($scanResult));
    }

    private function isValidJson($json)
    {
        $result = json_decode($json);

        return json_last_error() === JSON_ERROR_NONE;
    }

    public function testCanScanPathAndCreateValidResponseFile()
    {
        $scanner = new Scanner(new JsonResponse());
        $scanner->scan($this->sampleDirPath, $this->outputJsonPath);

        $this->assertTrue(file_exists($this->outputJsonPath));

        $result = file_get_contents($this->outputJsonPath);
        $this->assertTrue($this->isValidJson($result));
    }

    public function testCanPopulatePathUsingInputFile()
    {
        $scanner = new Scanner(new JsonResponse());
        $scanner->populate($this->basePathToPopulate, $this->sampleJson);

        $this->assertFileExists($this->populatedFile);
    }

    /**
     * @expectedException \KamranAhmed\Smasher\Exceptions\UnreadablePathException
     */
    public function testThrowsExceptionTryingToScanInvalidPath()
    {
        $scanner = new Scanner(new JsonResponse());
        $scanner->scan($this->invalidDirPath);
    }

    public function testCanProbePathAndGenerateArrayOfContent()
    {
        $scanner = new Scanner(new JsonResponse());
        $output  = [];

        $this->callProtectedMethod($scanner, 'probe', [
            $this->sampleDirPath,
            &$output,
        ]);

        // Verifying that a valid array is returned by checking
        // Orchestrate a better way to verify this array.
        $this->assertTrue(isset($output['sample-path']['child-item']['grand-child']['child-file.md']));
    }

    public static function callProtectedMethod($object, $method, array $args = [])
    {
        $class  = new ReflectionClass(get_class($object));
        $method = $class->getMethod($method);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $args);
    }

    /**
     * @expectedException \KamranAhmed\Smasher\Exceptions\InvalidContentException
     */
    public function testGettingContentThrowsExceptionForInvalidFile()
    {
        $scanner = new Scanner(new JsonResponse());
        $this->callProtectedMethod($scanner, 'getScannedContent', [
            $this->invalidScanSample,
        ]);
    }

    /**
     * @expectedException \KamranAhmed\Smasher\Exceptions\NoContentException
     */
    public function testGettingContentThrowsExceptionForEmptyFile()
    {
        $scanner = new Scanner(new JsonResponse());
        $this->callProtectedMethod($scanner, 'getScannedContent', [
            $this->emptyScanSample,
        ]);
    }

    public function testCanGetScannedContentFromJsonFile()
    {
        $scanner = new Scanner(new JsonResponse());
        $output  = [];

        $scannedArray = $this->callProtectedMethod($scanner, 'getScannedContent', [
            $this->sampleJson,
        ]);

        // Verifying that a valid array is returned by checking
        // Orchestrate a better way to verify this array.
        $this->assertTrue(isset($scannedArray['sample-path']['child-item']['grand-child']['child-file.md']));
    }

    protected function tearDown()
    {
        if (file_exists($this->populatedDir)) {
            $this->removeDirectory($this->populatedDir);
        }

        if (file_exists($this->outputJsonPath)) {
            unlink($this->outputJsonPath);
        }
    }

    private function removeDirectory($directory)
    {
        $iterator = new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS);
        $files    = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }

        rmdir($directory);
    }
}
