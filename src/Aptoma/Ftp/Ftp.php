<?php
namespace Aptoma\Ftp;

use Aptoma\Ftp\Exception\FtpException;
use Aptoma\Ftp\Exception\VerifySizeException;
use Psr\Log\LoggerInterface;

/**
 * Ftp wraps the native ftp functions in a nicer OO interface
 */
class Ftp
{
    private $connection;
    /**
     * @var LoggerInterface
     */
    private $logger;

    private $hostname;
    private $username;
    private $password;

    public function __construct($hostname, $username, $password, LoggerInterface $logger)
    {
        if (!extension_loaded('ftp')) {
            throw new FtpException('Missing FTP PHP extension.');
        }
        $this->hostname = $hostname;
        $this->username = $username;
        $this->password = $password;
        $this->logger = $logger;
    }
    
    /**
     * @param bool $state
     * @return bool
     */
    public function setPassiveMode($state)
    {
        return ftp_pasv($this->getConnection(), $state);
    }

    /**
     * @param $path
     */
    public function mkdir($path)
    {
        $this->logger->info('mkdir ' . $path);
        $parts = explode('/', $path);

        $mkTemp = '';
        foreach ($parts as $dir) {
            if ($dir == '') {
                continue;
            }
            $mkTemp .= '/' . $dir;
            $this->logger->debug('mkdir: ' . $mkTemp);
            if (@ftp_mkdir($this->getConnection(), $mkTemp)) {
                $this->logger->debug('mkdir success');
            }
        }
    }

    /**
     * @param $target
     * @param $origin
     * @param int $mode
     * @return bool
     */
    public function put($target, $origin, $mode = FTP_BINARY)
    {
        $this->logger->debug(sprintf('FTP: put from %s to %s', $origin, $target));
        return ftp_put($this->getConnection(), $target, $origin, $mode);
    }

    /**
     * @param $source
     * @param $target
     * @return bool
     */
    public function move($source, $target)
    {
        $this->logger->debug(sprintf('FTP: move from %s to %s', $source, $target));
        return ftp_rename($this->getConnection(), $source, $target);
    }

    /**
     * @param $path
     * @return bool
     */
    public function delete($path)
    {
        $this->logger->debug('FTP: delete ' . $path);
        return ftp_delete($this->getConnection(), $path);
    }

    /**
     * @param array $paths
     */
    public function preparePaths(array $paths)
    {
        foreach ($paths as $path) {
            $this->logger->debug('FTP: preparePath ' . $path);
            $this->mkdir(dirname($path));
        }
    }

    /**
     * @param $file
     * @return int
     */
    public function getSize($file)
    {
        return ftp_size($this->getConnection(), $file);
    }

    /**
     * @param $originSize
     * @param $target
     * @param null $tempTarget
     * @return bool
     */
    public function checkIfAlreadyUploaded($originSize, $target, $tempTarget = null)
    {
        $this->logger->debug('FTP: check if already uploaded', array($originSize, $target, $tempTarget));
        $targetSize = $this->getSize($target);
        $this->logger->debug('Public size: ' . $targetSize);
        $this->logger->debug('Origin size: ' . $originSize);
        if ($targetSize > 0 && $targetSize === $originSize) {
            $this->logger->info('Remote file exists, and has same size. Return.');

            return true;
        }
        if ($targetSize > 0 && $targetSize !== $originSize) {
            $this->logger->info('Remote file exists, but has different size. Remove and replace.');
            $this->delete($target);
        }

        if ($tempTarget) {
            $this->logger->debug('Temp target provided, check this one.');
            if ($this->checkIfAlreadyUploaded($originSize, $tempTarget)) {
                return $this->move($tempTarget, $target);
            } else {
                return false;
            }
        } else {
            $this->logger->debug('No temp target provided, upload needed.');
            return false;
        }
    }

    /**
     * Verify that uploaded file is the same as origin file, and if so, move to public folder
     *
     * @param $originSize
     * @param $tmpDestination
     * @param $publicDestination
     * @return bool
     * @throws FtpException
     * @throws VerifySizeException
     */
    public function verifyAndMoveUploadedFile(
        $originSize,
        $tmpDestination,
        $publicDestination
    ) {
        $remoteTempSize = $this->getSize($tmpDestination);
        $this->logger->debug('Temp size: ' . $remoteTempSize);
        $this->logger->debug('Origin size: ' . $originSize);
        if ($remoteTempSize <= 0) {
            throw new VerifySizeException('Uploaded file has size ' . $remoteTempSize);
        }

        if ($remoteTempSize !== $originSize) {
            throw new VerifySizeException(
                sprintf(
                    'Uploaded file has wrong size. Expected %s, got %s.',
                    $originSize,
                    $remoteTempSize
                )
            );
        }

        $this->logger->info('OK: Uploaded temp file has right size.');
        if (!$this->move($tmpDestination, $publicDestination)) {
            throw new FtpException('Error renaming uploaded file from temp to public.');
        }

        $remotePublicSize = $this->getSize($publicDestination);
        $this->logger->debug('Renamed size: ' . $remotePublicSize);
        if ($remotePublicSize <= 0) {
            throw new VerifySizeException('Renamed file has size ' . $remotePublicSize);
        }

        if ($remotePublicSize !== $originSize) {
            throw new VerifySizeException(
                sprintf(
                    'Renamed file has wrong size. Expected %s, got %s.',
                    $originSize,
                    $remotePublicSize
                )
            );
        }
        $this->logger->info('OK: Renamed file has right size.');

        return true;
    }

    /**
     * @return resource
     * @throws Exception\FtpException
     */
    private function getConnection()
    {
        if (!$this->connection) {
            $this->connection = ftp_connect($this->hostname);
            if (!$this->connection) {
                throw new FtpException(sprintf('Error connecting to FTP server at %s.', $this->hostname));
            }

            ftp_login($this->connection, $this->username, $this->password);
        }

        return $this->connection;
    }
}
