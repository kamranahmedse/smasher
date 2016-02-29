<?php

namespace KamranAhmed\Smasher;

class PathTest extends \PHPUnit_Framework_TestCase
{
    private $sampleFilePath;
    private $sampleDirPath;
    private $sampleFileCreate;
    private $invalidPath;

    public function setUp()
    {
        $this->sampleFilePath   = __DIR__ . '/data/scanned-samples/scanned-json.json';
        $this->sampleDirPath    = __DIR__ . '/data/output';
        $this->sampleFileCreate = __DIR__ . '/data/output/new-file.txt';
        $this->invalidPath      = __DIR__ . '/data/non/existing/path';
    }

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
     * @expectedException \KamranAhmed\Smasher\Exceptions\UnreadablePathException
     */
    public function testInvalidPathFailsValidation()
    {
        $path = new Path($this->invalidPath);
        $path->validate();
    }

    public function testCanCreateFileAndSaveContent()
    {
        $path = new Path($this->sampleFileCreate);
        $path->saveFileContent("Test content in file");
        $this->assertFileExists($this->sampleFilePath);
    }

    /**
     * @expectedException \KamranAhmed\Smasher\Exceptions\InvalidPathException
     */
    public function testCannotWriteContentToDirectoryPath()
    {
        $path = new Path($this->sampleDirPath);
        $path->saveFileContent("Test content in directory");
    }

    public function testCanGetPath()
    {
        $path = new Path($this->sampleFilePath);
        $this->assertEquals($this->sampleFilePath, $path->getPath());
    }

    /**
     * @dataProvider itemTypesProvider
     */
    public function testCanCreateItemsAndGetDetails($toCreate, $detail)
    {
        $path = new Path($toCreate);
        $path->createItem($detail);

        // Check if file was created
        $this->assertFileExists($toCreate);

        $detail = $path->getDetail();

        // Check if the detail has everything that we need
        $this->assertArrayHasKey('@name', $detail);
        $this->assertArrayHasKey('@path', $detail);
        $this->assertArrayHasKey('@type', $detail);
        $this->assertArrayHasKey('@size', $detail);
        $this->assertArrayHasKey('@mode', $detail);
        $this->assertArrayHasKey('@owner', $detail);
        $this->assertArrayHasKey('@last_modified', $detail);
        $this->assertArrayHasKey('@group', $detail);

        // Verifying the get methods
        $this->assertEquals($detail['@name'], $path->getName());
        $this->assertEquals($detail['@type'], $path->getType());
        $this->assertInternalType('int', $path->getSize());
        $this->assertRegexp('/\d{4}/', $path->getMode());
        $this->assertInternalType('array', $path->getOwner());
        $this->assertInternalType('string', $path->getLastModified());
        $this->assertInternalType('array', $path->getGroup());
        $this->assertNotEmpty('string', $path->getRealPath());

        $type = $path->getType();

        if ($type === 'file') {
            $content = $path->getFileContent();
            $this->assertEquals($detail['@content'], $content);
        }

        if ($type == 'dir') {
            rmdir($toCreate);
        } elseif ($type == 'file' || $type == 'link') {
            unlink($toCreate);
        }
    }

    public function itemTypesProvider()
    {
        return [
            [
                __DIR__ . '/data/output/test-dir',
                [
                    '@type' => 'dir',
                    '@name' => 'test-dir',
                ],
            ], [
                __DIR__ . '/data/output/test-file.txt',
                [
                    '@type'    => 'file',
                    '@content' => 'lorem ipsum',
                    '@name'    => 'test-file.txt',
                ],
            ], [
                __DIR__ . '/data/output/test-link',
                [
                    '@type'        => 'link',
                    '@destination' => __DIR__ . '/data/sample-path',
                    '@name'        => 'test-file.txt',
                ],
            ],
        ];
    }

    protected function tearDown()
    {
        if (file_exists($this->sampleFileCreate)) {
            unlink($this->sampleFileCreate);
        }
    }
}
