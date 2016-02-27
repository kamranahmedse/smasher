<?php

namespace KamranAhmed\SquashDir;

use ReflectionClass;

class ScannerTest extends \PHPUnit_Framework_TestCase
{
    private $sampleDirPath  = __DIR__ . '/data/sample-path';
    private $outputJsonPath = __DIR__ . '/data/output/sample-path.json';

    public function testCanCrawlPathAndGetJsonResult()
    {
        $spider      = new Scanner(new JsonResponse());
        $crawlResult = $spider->scanPath($this->sampleDirPath);

        $this->assertTrue($this->isValidJson($crawlResult));
    }

    private function isValidJson($json)
    {
        $result = json_decode($json);

        return json_last_error() === JSON_ERROR_NONE;
    }

    public function testCanCrawlPathAndCreateValidJsonResponseFile()
    {
        $sourceToConvert = $this->sampleDirPath;
        $outputFile      = $this->outputJsonPath;

        $spider = new Scanner(new JsonResponse());
        $spider->scanPath($sourceToConvert, $outputFile);

        $this->assertTrue(file_exists($outputFile));

        $result = file_get_contents($outputFile);
        $this->assertTrue($this->isValidJson($result));
    }

    public function testSpiderCanProbePathAndGenerateArrayOfContent()
    {
        $spider = new Scanner(new JsonResponse());
        $output = [];

        $this->callProtectedMethod($spider, 'probePath', [
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
}

