<?php

require $GLOBALS['APP_DIR'] . '/code/community/Sendit/Bliskapaczka/Model/Carrier/Bliskapaczka.php';
require $GLOBALS['ROOT_DIR'] . '/vendor/bliskapaczkapl/bliskapaczka-api-client/src/Bliskapaczka/ApiClient/Logger.php';
require $GLOBALS['ROOT_DIR'] .
    '/vendor/bliskapaczkapl/bliskapaczka-api-client/src/Bliskapaczka/ApiClient/ApiCaller/ApiCaller.php';
require $GLOBALS['ROOT_DIR'] .
    '/vendor/bliskapaczkapl/bliskapaczka-api-client/src/Bliskapaczka/ApiClient/Bliskapaczka.php';


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

        $this->apiClient = $this->getMockBuilder(\Bliskapaczka\ApiClient\Bliskapaczka::class)
                                     ->disableOriginalConstructor()
                                     ->disableOriginalClone()
                                     ->disableArgumentCloning()
                                     ->disallowMockingUnknownTypes()
                                     ->setMethods(array('getPricing'))
                                     ->getMock();
        $this->apiClient->method('getPricing')->will($this->returnValue(''));

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
