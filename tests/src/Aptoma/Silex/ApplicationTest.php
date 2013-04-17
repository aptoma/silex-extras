<?php

namespace Aptoma\Silex;

use Aptoma\Silex\Mocks\Application as MockApplication;
use Monolog\Handler\TestHandler;
use Silex\Provider\MonologServiceProvider;
use Symfony\Component\HttpFoundation\Request;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider logExecTimeDataProvider
     */
    public function testLogExecTimeShouldLogAtDifferentLevelsDependingOnExecTime($thresholds, $methodToCheck)
    {
        $app = new Application(
            array_merge(
                array('monolog.logfile' => false, 'timer.start' => microtime(true)),
                $thresholds
            )
        );

        $app->register(
            new MonologServiceProvider(),
            array(
                'monolog.handler' => new TestHandler()
            )
        );
        usleep(1001);
        $request = new Request(array(), array(), array(), array(), array(), array('QUERY_STRING' => 'foo=bar'));
        $app->logExecTime($request);
        $records = $app['monolog.handler']->getRecords();

        $this->assertTrue($app['monolog.handler']->{$methodToCheck}());
        $this->assertEquals('foo=bar', $records[0]['context']['query']);
    }

    public function logExecTimeDataProvider()
    {
        return array(
            'debug' => array(
                array(
                    'timer.threshold_info' => 1000,
                    'timer.threshold_warning' => 5000,
                ),
                'hasDebugRecords',
            ),
            'info' => array(
                array(
                    'timer.threshold_info' => 1,
                    'timer.threshold_warning' => 5000,
                ),
                'hasInfoRecords',
            ),
            'warning' => array(
                array(
                    'timer.threshold_info' => 0,
                    'timer.threshold_warning' => 1,
                ),
                'hasWarningRecords',
            ),
        );
    }

    public function testRegisterLoggerShouldNotDoAnythingIfNameIsNotSet()
    {
        require_once 'Mocks/Application.php';
        $app = new MockApplication(array());

        $this->assertFalse($app->offsetExists('monolog'));
    }

    public function testRegisterLoggerShouldRegisterLogger()
    {
        require_once 'Mocks/Application.php';
        $app = new MockApplication(
            array(
                'monolog.name' => 'test',
                'monolog.level' => 100,
                'monolog.logfile' => false,
            )
        );

        $this->assertTrue($app->offsetExists('monolog'));
    }

    public function testRegisterTwigShouldDoNothingIfNoTemplatePathIsSet()
    {
        require_once 'Mocks/Application.php';
        $app = new MockApplication(array());

        $this->assertFalse($app->offsetExists('twig'));
    }

    public function testRegisterTwigShouldRegisterTwig()
    {
        require_once 'Mocks/Application.php';
        $app = new MockApplication(array('twig.path' => __DIR__, 'twig.options' => array()));

        $this->assertTrue($app->offsetExists('twig'));
    }

    public function testRegisterTwigShouldAddAppExtensionIfAvailable()
    {
        require_once 'Mocks/Application.php';

        $app = new MockApplication(array('twig.path' => __DIR__, 'twig.options' => array()));
        $this->assertFalse($app['twig']->hasExtension('app'));

        require_once 'Mocks/AppExtension.php';
        $app = new MockApplication(array('twig.path' => __DIR__, 'twig.options' => array()));
        $this->assertTrue($app['twig']->hasExtension('app'));
    }
}
