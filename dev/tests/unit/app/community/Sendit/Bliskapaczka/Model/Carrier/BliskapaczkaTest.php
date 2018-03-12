<?php

require $GLOBALS['APP_DIR'] . '/code/community/Sendit/Bliskapaczka/Model/Carrier/Abstract.php';
require $GLOBALS['APP_DIR'] . '/code/community/Sendit/Bliskapaczka/Model/Carrier/Bliskapaczka.php';

use PHPUnit\Framework\TestCase;
use Bliskapaczka\ApiClient;

class BliskapaczkaTest extends TestCase
{
    protected $shippingCode = 'sendit_bliskapaczka';
    protected $request;

    protected function setUp()
    {
        $this->request = $this->getMockBuilder(Mage_Shipping_Model_Rate_Request::class)
                                     ->disableOriginalConstructor()
                                     ->disableOriginalClone()
                                     ->disableArgumentCloning()
                                     ->disallowMockingUnknownTypes()
                                     ->getMock();

        $this->helper = $this->getMockBuilder(Sendit_Bliskapaczka_Helper_Data::class)
                                     ->disableOriginalConstructor()
                                     ->disableOriginalClone()
                                     ->disableArgumentCloning()
                                     ->disallowMockingUnknownTypes()
                                     ->setMethods(array('getParcelDimensions'))
                                     ->getMock();
        $this->helper->method('getParcelDimensions')->will($this->returnValue(''));

        $this->apiClientOrder = $this->getMockBuilder(\Bliskapaczka\ApiClient\Bliskapaczka\Pricing::class)
                                     ->disableOriginalConstructor()
                                     ->disableOriginalClone()
                                     ->disableArgumentCloning()
                                     ->disallowMockingUnknownTypes()
                                     ->setMethods(array('get'))
                                     ->getMock();
        $this->apiClientOrder->method('get')->will($this->returnValue(''));

    }

    public function testClassExists()
    {
        $this->assertTrue(class_exists('Sendit_Bliskapaczka_Model_Carrier_Bliskapaczka'));
    }

    public function testClassImplementInterface()
    {
        $this->assertTrue(method_exists('Sendit_Bliskapaczka_Model_Carrier_Bliskapaczka', 'collectRates'));
        $this->assertTrue(method_exists('Sendit_Bliskapaczka_Model_Carrier_Bliskapaczka', 'getAllowedMethods'));
    }

    public function testGetAllowedMethods()
    {
        $bp = new Sendit_Bliskapaczka_Model_Carrier_Bliskapaczka();
        $allowedShippingMethods = $bp->getAllowedMethods();

        $this->assertTrue(is_array($allowedShippingMethods));
        $this->assertTrue(array_key_exists($this->shippingCode, $allowedShippingMethods));
    }
}
