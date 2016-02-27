<?php

namespace KamranAhmed\SquashDir;

use ReflectionClass;

class ScannerTest extends \PHPUnit_Framework_TestCase
{
    private $sampleDirPath  = __DIR__ . '/data/sample-path';
    private $invalidDirPath = __DIR__ . '/invalid/path/that/does/not/exist';
    private $outputJsonPath = __DIR__ . '/data/output/sample-path.json';

    private $invalidScanSample = __DIR__ . '/data/scanned-samples/invalid-scan.md';
    private $emptyScanSample   = __DIR__ . '/data/scanned-samples/empty-scan.json';
    private $sampleJson        = __DIR__ . '/data/scanned-samples/scanned-json.json';

    public function testCanScanPathAndGetJsonResult()
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

    /**
     * @expectedException \KamranAhmed\SquashDir\Exceptions\InvalidPathException
     */
    public function testThrowsExceptionTryingToScanInvalidPath()
    {
        $scanner = new Scanner(new JsonResponse());
        $scanner->scanPath($this->invalidDirPath);
    }

    public function testCanCrawlPathAndCreateValidJsonResponseFile()
    {
        $sourceToConvert = $this->sampleDirPath;
        $outputFile      = $this->outputJsonPath;

        $scanner = new Scanner(new JsonResponse());
        $scanner->scanPath($sourceToConvert, $outputFile);

        $this->assertTrue(file_exists($outputFile));

        $result = file_get_contents($outputFile);
        $this->assertTrue($this->isValidJson($result));
    }

    public function testSpiderCanProbePathAndGenerateArrayOfContent()
    {
        $scanner = new Scanner(new JsonResponse());
        $output = [];

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
        $output = [];

        $scannedArray = $this->callProtectedMethod($scanner, 'getScannedContent', [
            $this->sampleJson,
        ]);

        // Verifying that a valid array is returned by checking
        // Orchestrate a better way to verify this array.
        $this->assertTrue(isset($scannedArray['sample-path']['child-item']['grand-child']['child-file.md']));
    }
}

