<?php

namespace Aptoma\Storage;

use Monolog\Logger;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileStorageTest extends \PHPUnit_Framework_TestCase
{
    private $storageDir;
    private $publicUrlTemplate = 'http://www.example.com/files/{assetId}/raw';

    protected function setUp()
    {
        $this->storageDir = '/tmp/' . uniqid('silex-extras-' . time() . '-');
    }

    public function testFileStorageShouldImplementStorageInterface()
    {
        $service = $this->createService();
        $this->assertInstanceOf('\Aptoma\Storage\StorageInterface', $service);
    }

    public function testPutShouldStoreFileInSpecifiedDirectory()
    {
        $service = $this->createService();
        $fileToRead = __DIR__ . '/../fixtures/topgun.jpg';
        $resource = fopen($fileToRead, 'rb');
        $identifier = $service->put($resource);

        $this->assertFileExists($this->storageDir . '/' . $identifier);
        $this->assertEquals(filesize($fileToRead), filesize($this->storageDir . '/' . $identifier));
    }

    public function testPutShouldHandleUploadFileObject()
    {
        $fileToRead = __DIR__ . '/../fixtures/topgun.jpg';
        $file = new UploadedFile($fileToRead, 'topgun.jpg');
        $service = $this->createService();

        try {
            $service->put($file);
            $this->fail('Put should fail for uploaded file.');
        } catch (FileException $e) {
            return;
        }

        $this->fail('Put should fail throw FileException.');
    }

    public function testGetMimeTypeShouldReturnMimeTypeOfAsset()
    {
        $service = $this->createService();
        $resource = fopen(__DIR__ . '/../fixtures/topgun.jpg', 'rb');
        $identifier = $service->put($resource);

        $this->assertEquals('image/jpeg', $service->getMimeType($identifier));
    }

    public function testGetUrlShouldReturnUrlToFile()
    {
        $service = $this->createService();

        $this->assertEquals('http://www.example.com/files/filename.jpg/raw', $service->getUrl('filename.jpg'));
    }

    /**
     * @expectedException \Aptoma\Storage\Exception\FileNotFoundException
     */
    public function testGetRawShouldThrowExceptionForNonExistingFile()
    {
        $service = $this->createService();
        $service->getRaw('unkown');
    }

    public function testGetRawTypeShouldReturnRawDataEqualToStoredData()
    {
        $service = $this->createService();
        $resource = file_get_contents(__DIR__ . '/../fixtures/topgun.jpg');
        $identifier = $service->put($resource);

        $this->assertEquals($resource, $service->getRaw($identifier));
    }

    public function testGetRawTypeShouldReturnResourceWhenAsResourceArgumentIsProvided()
    {
        $service = $this->createService();
        $resource = file_get_contents(__DIR__ . '/../fixtures/topgun.jpg');
        $identifier = $service->put($resource);

        $this->assertInternalType('resource', $service->getRaw($identifier, true));
    }

    /**
     * @return FileStorage
     */
    private function createService()
    {
        $logger = new Logger('test');

        $service = new FileStorage($this->storageDir, $this->publicUrlTemplate, $logger);

        return $service;
    }
}
