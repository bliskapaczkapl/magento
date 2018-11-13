<?php

require $GLOBALS['APP_DIR'] . '/code/community/Sendit/Bliskapaczka/Helper/Tracking.php';

use PHPUnit\Framework\TestCase;

class TrackingTest extends TestCase
{
    public function testClassExists()
    {
        $this->assertTrue(class_exists('Sendit_Bliskapaczka_Helper_Tracking'));
    }

    /**
     * @dataProvider modeForTracking
     */
    public function testTrackingLingForMode($mode, $expected)
    {
        $senditHelper = $this->getMockBuilder(Sendit_Bliskapaczka_Helper_Data::class)
            ->setMethods(array('getApiMode'))
            ->getMock();
        $senditHelper->method('getApiMode')->will($this->returnValue($mode));

        $trackingNumber = '00159007738100208115';

        $helper = new Sendit_Bliskapaczka_Helper_Tracking();
        $this->assertEquals($expected, $helper->getLink($trackingNumber, $senditHelper));
    }

    public function modeForTracking()
    {
        return [
            ['test', "https://sandbox-bliskapaczka.pl/sledzenie/00159007738100208115"],
            ['prod', "https://bliskapaczka.pl/sledzenie/00159007738100208115"]
        ];
    }
}
