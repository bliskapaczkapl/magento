<?php

require $GLOBALS['APP_DIR'] . '/code/community/Sendit/Bliskapaczka/Helper/Api.php';

use PHPUnit\Framework\TestCase;

class ApiTest extends TestCase
{
    public function testClassExists()
    {
        $this->assertTrue(class_exists('Sendit_Bliskapaczka_Helper_Api'));
    }

    public function testClassExtendMageCoreHelperData()
    {
        $hepler = new Sendit_Bliskapaczka_Helper_Api();
        $this->assertTrue($hepler instanceof Mage_Core_Helper_Data);
    }

    /**
     * @dataProvider shippingMethodAndAdive
     */
    public function testGetApiClientForOrderMethodName($method, $autoAdvice, $receiverValidator, $result)
    {
        $this->helper = $this->getMockBuilder(Sendit_Bliskapaczka_Helper_Data::class)
                                     ->disableOriginalConstructor()
                                     ->disableOriginalClone()
                                     ->disableArgumentCloning()
                                     ->disallowMockingUnknownTypes()
                                     ->setMethods(array('isPoint'))
                                     ->getMock();

        $map = array(
            array('bliskapaczka_sendit_bliskapaczka', true),
            array('bliskapaczka_sendit_bliskapaczka_COD', true),
            array('bliskapaczka_courier_sendit_bliskapaczka_courier', false)
        );

        $this->helper->method('isPoint')->will($this->returnValueMap($map));


        $hepler = new Sendit_Bliskapaczka_Helper_Api();
        $this->assertEquals(
            $result,
            $hepler->getApiClientForOrderMethodName($method, $autoAdvice, $receiverValidator, $this->helper)
        );
    }

    public function shippingMethodAndAdive()
    {
        return [
            ['bliskapaczka_sendit_bliskapaczka', '0', '0', 'getApiClientOrder'],
            ['bliskapaczka_sendit_bliskapaczka', '1', '0', 'getApiClientOrderAdvice'],
            ['bliskapaczka_sendit_bliskapaczka_COD', '0', '0', 'getApiClientOrder'],
            ['bliskapaczka_sendit_bliskapaczka_COD', '1', '0', 'getApiClientOrderAdvice'],
            ['bliskapaczka_courier_sendit_bliskapaczka_courier', '0', '0', 'getApiClientTodoor'],
            ['bliskapaczka_courier_sendit_bliskapaczka_courier', '1', '0', 'getApiClientTodoorAdvice'],
            ['sendit_bliskapaczka_courier_GLS', '0', '1', 'getApiClientTodoorReceiverValidator'],
            ['bliskapaczka_sendit_bliskapaczka', '0', '1', 'getApiClientOrderReceiverValidator']
        ];
    }
}
