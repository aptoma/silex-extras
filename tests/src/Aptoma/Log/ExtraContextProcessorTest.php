<?php

namespace Aptoma\Log;

use \Silex\Application;
use PHPUnit\Framework\TestCase;

class ExtraContextProcessorTest extends TestCase
{

    /**
     * @param $config
     * @param $expected
     * @dataProvider invokeDataProvider
     */
    public function testInvokeShouldAddFieldsSetInApp($config, $expected)
    {
        $processor = new ExtraContextProcessor($config);

        $this->assertEquals($expected, $processor(array()));
    }

    public function invokeDataProvider()
    {
        return array(
            'no values' => array (
                array(), array()
            ),
            'single value' => array (
                array('service' => 'admin'), array('extra' => array('service' => 'admin'))
            ),
            'multiple values' => array (
                array('service' => 'admin', 'customer' => 'Aptoma', 'environment' => 'test'),
                array('extra' => array('service' => 'admin', 'customer' => 'Aptoma', 'environment' => 'test'))
            ),
        );
    }
}
