<?php

namespace KamranAhmed\SquashDir;

class PathTest extends \PHPUnit_Framework_TestCase
{
    private $sampleFilePath   = __DIR__ . '/data/scanned-samples/scanned-json.json';
    private $sampleDirPath    = __DIR__ . '/data/output';
    private $sampleFileCreate = __DIR__ . '/data/output/new-file.txt';
    private $invalidPath      = __DIR__ . '/data/non/existing/path';

    public function testCanSetPath()
    {
        $path = new Path($this->sampleFilePath);

        $actualPath = $path->getPath();
        $this->assertEquals($this->sampleFilePath, $actualPath);

        // Check if the path can be updated through `setPath`
        $path->setPath($this->sampleDirPath);
        $actualPath = $path->getPath();

        $this->assertEquals($this->sampleDirPath, $actualPath);
    }

    public function testValidPathPassesValidation()
    {
        $path      = new Path($this->sampleFilePath);
        $validated = $path->validate();

        $this->assertTrue($validated);
    }

    /**
     * @expectedException \KamranAhmed\SquashDir\Exceptions\InvalidPathException
     */
    public function testInvalidPathFailsValidation()
    {
        $path = new Path($this->invalidPath);
        $path->validate();
    }

    public function testCanCreateFileAndSaveContent()
    {
        $path = new Path($this->sampleFileCreate);
        $path->saveFileContent("Testcontent in file");
        $this->assertFileExists($this->sampleFilePath);
    }

    protected function tearDown()
    {
        if (file_exists($this->sampleFileCreate)) {
            unlink($this->sampleFileCreate);
        }
    }
}

