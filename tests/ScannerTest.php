<?php

namespace KamranAhmed\SquashDir;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;

class ScannerTest extends \PHPUnit_Framework_TestCase
{
    private $sampleDirPath      = __DIR__ . '/data/sample-path';
    private $invalidDirPath     = __DIR__ . '/invalid/path/that/does/not/exist';
    private $outputJsonPath     = __DIR__ . '/data/output/sample-path.json';
    private $basePathToPopulate = __DIR__ . '/data/output/';
    private $populatedDir       = __DIR__ . '/data/output/sample-path';
    private $populatedFile      = __DIR__ . '/data/output/sample-path/child-item/grand-child/child-file.md';

    private $invalidScanSample = __DIR__ . '/data/scanned-samples/invalid-scan.md';
    private $emptyScanSample   = __DIR__ . '/data/scanned-samples/empty-scan.json';
    private $sampleJson        = __DIR__ . '/data/scanned-samples/scanned-json.json';

    public function testCanScanPathAndGetResult()
    {
        $scanner    = new Scanner(new JsonResponse());
        $scanResult = $scanner->scanPath($this->sampleDirPath);

        $this->assertTrue($this->isValidJson($scanResult));
    }

    private function isValidJson($json)
    {
        $result = json_decode($json);

        return json_last_error() === JSON_ERROR_NONE;
    }

    public function testCanScanPathAndCreateValidResponseFile()
    {
        $sourceToConvert = $this->sampleDirPath;
        $outputFile      = $this->outputJsonPath;

        $scanner = new Scanner(new JsonResponse());
        $scanner->scanPath($sourceToConvert, $outputFile);

        $this->assertTrue(file_exists($outputFile));

        $result = file_get_contents($outputFile);
        $this->assertTrue($this->isValidJson($result));
    }

    public function testCanPopulatePathUsingInputFile()
    {
        $scanner = new Scanner(new JsonResponse());
        $scanner->populatePath($this->basePathToPopulate, $this->outputJsonPath);

        $this->assertFileExists($this->populatedFile);
    }

    /**
     * @expectedException \KamranAhmed\SquashDir\Exceptions\InvalidPathException
     */
    public function testThrowsExceptionTryingToScanInvalidPath()
    {
        $scanner = new Scanner(new JsonResponse());
        $scanner->scanPath($this->invalidDirPath);
    }

    public function testCanProbePathAndGenerateArrayOfContent()
    {
        $scanner = new Scanner(new JsonResponse());
        $output  = [];

        $this->callProtectedMethod($scanner, 'probePath', [
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
     * @expectedException \KamranAhmed\SquashDir\Exceptions\InvalidContentException
     */
    public function testGettingContentThrowsExceptionForInvalidFile()
    {
        $scanner = new Scanner(new JsonResponse());
        $this->callProtectedMethod($scanner, 'getScannedContent', [
            $this->invalidScanSample,
        ]);
    }

    /**
     * @expectedException \KamranAhmed\SquashDir\Exceptions\NoContentException
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

