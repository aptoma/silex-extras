<?php

namespace Aptoma\Storage;

use Aptoma\Storage\Exception\FileNotFoundException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileStorage implements StorageInterface
{
    private $storageDirectory;
    private $publicUrlTemplate;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct($storageDirectory, $publicUrlTemplate, LoggerInterface $logger)
    {
        $this->storageDirectory = $storageDirectory;
        $this->publicUrlTemplate = $publicUrlTemplate;
        $this->logger = $logger;
    }

    /**
     * @param resource|string|UploadedFile $resource
     * @return string Asset identifier
     */
    public function put($resource)
    {
        $tmpFileName = tempnam('/tmp', 'test_');
        if ($resource instanceof UploadedFile) {
            $pathinfo = pathinfo($tmpFileName);
            $resource->move($pathinfo['dirname'], $pathinfo['basename']);
        } else {
            file_put_contents($tmpFileName, $resource);
        }

        $checksum = sha1_file($tmpFileName);
        $file = new File($tmpFileName);
        $targetFileName = $checksum . '.' . $file->guessExtension();
        $target = $file->move($this->storageDirectory, $targetFileName);
        $filesystem = new Filesystem();

        $filesystem->chmod($target, 0777);

        return $targetFileName;
    }

    /**
     * @param $identifier
     * @return string Url where resource can be read
     */
    public function getUrl($identifier)
    {
        return str_replace('{assetId}', $identifier, $this->publicUrlTemplate);
    }

    /**
     * @param $identifier
     * @param bool $asResource
     * @throws Exception\StorageException
     * @return string|resource The raw content or a resource to read the content stream.
     */
    public function getRaw($identifier, $asResource = false)
    {
        $source = $this->getSourceFileName($identifier);
        if (!file_exists($source)) {
            throw new FileNotFoundException(sprintf('File `%s` does not exist.', $source));
        }

        if ($asResource) {
            return fopen($source, 'rb');
        } else {
            return file_get_contents($source);
        }
    }

    public function getMimeType($identifier)
    {
        $file = new File($this->getSourceFileName($identifier));

        return $file->getMimeType();
    }

    /**
     * @param $identifier
     * @return string
     */
    private function getSourceFileName($identifier)
    {
        return $this->storageDirectory . '/' . $identifier;
    }
}
