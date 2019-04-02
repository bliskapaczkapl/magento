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
    public function testGetApiClientForOrderMethodName(
        $shippingMethod,
        $paymentMethod,
        $advice,
        $autoAdvice,
        $receiverValidator,
        $autoDdviceForPayPal,
        $result
    ) {
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
            $hepler->getApiClientForOrderMethodName(
                $shippingMethod,
                $paymentMethod,
                $advice,
                $autoAdvice,
                $receiverValidator,
                $autoDdviceForPayPal,
                $this->helper
            )
        );
    }

    public function shippingMethodAndAdive()
    {
        return [
            ['bliskapaczka_sendit_bliskapaczka', 'some_payment_method', '0', '0',  '0', '0', 'getApiClientOrder'],
            ['bliskapaczka_sendit_bliskapaczka', 'some_payment_method', '0', '1',  '0', '0', 'getApiClientOrderAdvice'],
            ['bliskapaczka_sendit_bliskapaczka_COD', 'some_payment_method', '0', '0', '0', '0', 'getApiClientOrder'],
            [
                'bliskapaczka_sendit_bliskapaczka_COD',
                'some_payment_method',
                '0',
                '1',
                '0',
                '0',
                'getApiClientOrderAdvice'
            ],
            [
                'bliskapaczka_courier_sendit_bliskapaczka_courier',
                'some_payment_method',
                '0',
                '0',
                '0',
                '0',
                'getApiClientTodoor'
            ],
            [
                'bliskapaczka_courier_sendit_bliskapaczka_courier',
                'some_payment_method',
                '0',
                '1',
                '0',
                '0',
                'getApiClientTodoorAdvice'
            ],
            [
                'sendit_bliskapaczka_courier_GLS',
                'some_payment_method',
                '0',
                '0',
                '1',
                '0',
                'getApiClientTodoorReceiverValidator'
            ],
            [
                'bliskapaczka_sendit_bliskapaczka',
                'some_payment_method',
                '0',
                '0',
                '1',
                '0',
                'getApiClientOrderReceiverValidator'
            ],
            ['bliskapaczka_sendit_bliskapaczka_COD', 'paypal_express_bml', '0', '1', '0', '1', 'getApiClientOrder'],
            [
                'bliskapaczka_sendit_bliskapaczka_COD',
                'paypal_express_bml',
                '0',
                '1',
                '0',
                '0',
                'getApiClientOrderAdvice'
            ],
            ['bliskapaczka_sendit_bliskapaczka_COD', 'paypal_express', '0', '1', '0', '1', 'getApiClientOrder'],
            ['bliskapaczka_sendit_bliskapaczka_COD', 'paypal_express', '0', '1', '0', '0', 'getApiClientOrderAdvice'],
            ['bliskapaczka_sendit_bliskapaczka_COD', 'paypal_direct', '0', '1', '0', '1', 'getApiClientOrder'],
            ['bliskapaczka_sendit_bliskapaczka_COD', 'paypal_direct', '0', '1', '0', '0', 'getApiClientOrderAdvice'],
            ['bliskapaczka_sendit_bliskapaczka_COD', 'paypal_standard', '0', '1', '0', '1', 'getApiClientOrder'],
            ['bliskapaczka_sendit_bliskapaczka_COD', 'paypal_standard', '0', '1', '0', '0', 'getApiClientOrderAdvice'],
            ['bliskapaczka_sendit_bliskapaczka_COD', 'paypaluk_express', '0', '1', '0', '1', 'getApiClientOrder'],
            ['bliskapaczka_sendit_bliskapaczka_COD', 'paypaluk_express', '0', '1', '0', '0', 'getApiClientOrderAdvice'],
            ['bliskapaczka_sendit_bliskapaczka_COD', 'paypaluk_direct', '0', '1', '0', '1', 'getApiClientOrder'],
            ['bliskapaczka_sendit_bliskapaczka_COD', 'paypaluk_direct', '0', '1', '0', '0', 'getApiClientOrderAdvice'],
            ['bliskapaczka_sendit_bliskapaczka_COD', 'paypaluk_express_bml', '0', '1', '0', '1', 'getApiClientOrder'],
            [
                'bliskapaczka_sendit_bliskapaczka_COD',
                'paypaluk_express_bml',
                '0',
                '1',
                '0',
                '0',
                'getApiClientOrderAdvice'
            ]
        ];
    }
}
