<?php

namespace Aptoma\Service\Level3;

use Aptoma\Ftp\Exception\FtpException;
use Aptoma\Ftp\Ftp;
use Aptoma\Service\Level3\Exception\Level3Exception;
use Aptoma\Service\Level3\Exception\UploadException;
use Aptoma\Storage\StorageInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Level3Service provides a simple abstraction for uploading content to Level3
 *
 * Currently, this is only basic ftp stuff, so unless we encounter any Level3
 * specific needs, this can probably be refactored into a generic FTP uploader.
 *
 * @author Gunnar Lium <gunnar@aptoma.com>
 */
class Level3Service implements StorageInterface
{
    private $localFolder;
    private $tmpFolder;
    private $publicFolder;
    private $publicUrl;
    private $maxUploadAttempts = 1;
    private $acceptedImageFormats = array('image/jpeg', 'image/png');
    private $uploadDisabled = true;
    private $deleteLocalCopyAfterUpload = false;

    /**
     * @var LoggerInterface
     */
    private $logger;
    private $ftp;

    /**
     * @param \Aptoma\Ftp\Ftp $ftp
     * @param $tmpFolder
     * @param $publicFolder
     * @param $publicUrl
     * @param LoggerInterface $logger
     * @param array $options
     */
    public function __construct(
        Ftp $ftp,
        $tmpFolder,
        $publicFolder,
        $publicUrl,
        LoggerInterface $logger,
        array $options = array()
    ) {
        $this->ftp = $ftp;
        $this->tmpFolder = $tmpFolder;
        $this->publicFolder = $publicFolder;
        $this->publicUrl = $publicUrl;
        $this->logger = $logger;
        if (isset($options['local_folder'])) {
            $this->localFolder = $options['local_folder'];
        }
        if (isset($options['accepted_image_formats'])) {
            $this->acceptedImageFormats = $options['accepted_image_formats'];
        }
        if (isset($options['max_upload_attempts'])) {
            $this->maxUploadAttempts = $options['max_upload_attempts'];
        }
        if (isset($options['upload_disabled'])) {
            $this->uploadDisabled = $options['upload_disabled'];
        }
        if (isset($options['delete_local_copy_after_upload'])) {
            $this->deleteLocalCopyAfterUpload = $options['delete_local_copy_after_upload'];
        }
        $this->ftp = $ftp;
    }

    /**
     * @param $value
     */
    public function setUploadDisabled($value)
    {
        $this->uploadDisabled = $value;
    }

    public function setDeleteLocalCopyAfterUpload($value)
    {
        $this->deleteLocalCopyAfterUpload = $value;
    }

    /**
     * @param resource|string $resource
     * @return string Asset identifier
     */
    public function put($resource)
    {
        if (is_resource($resource)) {
            $fileContents = stream_get_contents($resource);
        } else {
            $fileContents = $resource;
        }
        $name = uniqid('level3_');

        return $this->upload($fileContents, $name);
    }

    /**
     * @SuppressWarnings(UnusedFormalParameter)
     */
    public function getRaw($identifier, $asResource = false)
    {
        throw new \Exception('getRaw is not supported by Level3Service.');
    }

    /**
     * @SuppressWarnings(UnusedFormalParameter)
     */
    public function getMimeType($identifier)
    {
        throw new \Exception('getMimeType is not supported by Level3Service.');
    }

    /**
     * @param $identifier
     * @return string Url where resource can be read
     */
    public function getUrl($identifier)
    {
        return $this->publicUrl . '/' . $identifier;
    }

    /**
     * Store file contents to local disk, and upload to FTP
     *
     * @param $fileContents
     * @param $name
     * @throws Exception\Level3Exception
     * @return string Url to public version of file
     */
    public function upload($fileContents, $name)
    {
        if (!$this->localFolder) {
            throw new Level3Exception('Method `upload` requires that you have provided a `local_folder` $option');
        }

        $this->prepareDirectory($this->localFolder);

        $tempName = sprintf('%s/%s', $this->localFolder, $name);
        file_put_contents($tempName, $fileContents);
        $file = new File($tempName);
        $fs = new Filesystem();
        $fs->chmod($file, 0777);
        $this->logger->debug('Received file of type ' . $file->getMimeType());

        $this->verifyMimeType($file, $this->acceptedImageFormats);

        $filename = $this->generateFilename($file);

        if ($this->uploadDisabled) {
            $this->logger->info('Upload disabled. Returning filename: ' . $filename);
            if ($this->deleteLocalCopyAfterUpload) {
                $fs->remove($file);
            }

            return $filename;
        }

        $publicUrl = null;
        $start = microtime(true);
        $publicUrl = $this->putByName($file, $filename);
        $this->logger->info('OK: Upload to CDN completed in ' . round(microtime(true) - $start, 3) . 's');
        if ($this->deleteLocalCopyAfterUpload) {
            $fs->remove($file);
        }

        return $publicUrl;
    }

    /**
     * Generate a unique filename based on content of file.
     *
     * @param string|File $targetFile
     * @param int $levels Number of folders to generate
     * @param int $letters Number of letters to use in folder name
     * @return string
     */
    public function generateFilename(File $targetFile, $levels = 3, $letters = 3)
    {
        $sha = sha1_file($targetFile);

        $folder = '';
        for ($i = 0; $i < $levels * $letters; $i = $i + $letters) {
            $folder .= mb_substr($sha, $i, $letters) . '/';
        }

        return $folder . $sha . '.' . $targetFile->guessExtension();
    }

    /**
     * Upload file to FTP
     *
     * If file already exists, we do nothing, as it's assumed that files have
     * unique filenames, and an identical name would mean that the file is
     * identical to the one already uploaded.
     *
     * @param \Symfony\Component\HttpFoundation\File\File $originFile
     * @param $targetFile
     * @throws Exception\UploadException
     * @return string
     */
    public function putByName(File $originFile, $targetFile)
    {
        $publicUrl = $this->publicUrl . $targetFile;
        $tmpDestination = '/' . $this->tmpFolder . $targetFile;
        $publicDestination = '/' . $this->publicFolder . $targetFile;

        $this->ftp->preparePaths(array($tmpDestination, $publicDestination));

        $alreadyUploaded = $this->ftp->checkIfAlreadyUploaded(
            $originFile->getSize(),
            $publicDestination,
            $tmpDestination
        );
        if ($alreadyUploaded) {
            return $publicUrl;
        }

        $result = $this->ftp->put($tmpDestination, $originFile->getRealPath());
        if ($result) {
            $this->logger->info('FTP PUT succeeded.');
            try {
                $this->ftp->verifyAndMoveUploadedFile(
                    $originFile->getSize(),
                    $tmpDestination,
                    $publicDestination
                );
            } catch (FtpException $e) {
                $this->logger->error($e->getMessage());
                throw new UploadException('Error verifying uploaded file.');
            }
            $this->logger->warning('Skipping verification of public url: ' . $publicUrl);
        } else {
            $this->logger->error('Error uploading to temp');
            throw new UploadException('Error uploading file to storage.');
        }

        return $publicUrl;
    }

    private function prepareDirectory($dir)
    {
        if (!file_exists($dir)) {
            $this->logger->info('Creating directory ' . $dir);
            mkdir($dir, 0777, true);
        }
    }

    /**
     * Verify that $file has a supported mime type
     *
     * @param File $file
     * @param array $acceptedTypes
     * @throws HttpException
     */
    private function verifyMimeType(File $file, array $acceptedTypes)
    {
        if (!in_array($file->getMimeType(), $acceptedTypes)) {
            throw new HttpException(
                415,
                sprintf(
                    'Unsupported media type: %s, expected one of %s.',
                    $file->getMimeType(),
                    join(', ', $acceptedTypes)
                )
            );
        }
    }
}
