<?php
namespace Test;

use Aptoma\Ftp\Ftp;
use Aptoma\Service\Level3\Level3Service;
use Aptoma\TestToolkit\BaseWebTestCase;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\File\File;

class Level3ServiceTest extends BaseWebTestCase
{

    public function testLevel3ServiceShouldImplementAssetStorageInterface()
    {
        $service = $this->createService();
        $this->assertInstanceOf('\Aptoma\Storage\StorageInterface', $service);
    }

    /**
     * @dataProvider provider
     */
    public function testGenerateFilenameShouldCreateLevelsAndLettersAccordingToInput($expect, $levels, $letters)
    {
        $file = new File(__DIR__ . '/../../fixtures/topgun.jpg');
        $service = $this->createService();
        $this->assertEquals(
            $expect,
            $service->generateFilename($file, $levels, $letters)
        );
    }

    public function provider()
    {
        return array(
            '0x0' => array('a52518be79145eba434fd2a044723d48e01e4803.jpeg', 0, 0),
            '1x2' => array('a5/a52518be79145eba434fd2a044723d48e01e4803.jpeg', 1, 2),
            '1x3' => array('a52/a52518be79145eba434fd2a044723d48e01e4803.jpeg', 1, 3),
            '3x2' => array('a5/25/18/a52518be79145eba434fd2a044723d48e01e4803.jpeg', 3, 2),
            '3x3' => array('a52/518/be7/a52518be79145eba434fd2a044723d48e01e4803.jpeg', 3, 3),
        );
    }

    /**
     * @return Level3Service
     */
    private function createService()
    {
        $logger = new Logger('test');
        $ftp = new Ftp(null, null, null, $logger);
        $service = new Level3Service($ftp, null, null, null, $logger);
        return $service;
    }
}
