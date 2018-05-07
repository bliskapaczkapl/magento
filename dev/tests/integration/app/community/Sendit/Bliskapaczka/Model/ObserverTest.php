<?php

require $GLOBALS['APP_DIR'] . '/code/community/Sendit/Bliskapaczka/Model/Observer.php';

use PHPUnit\Framework\TestCase;

class ObserverTest extends TestCase
{
    protected function setUp()
    {
        $this->receiverFirstName = 'Zenek';
        $this->receiverLastName = 'Bliskopaczki';
        $this->receiverPhoneNumber = '504 445 665';
        $this->receiverEmail = 'zenek.bliskopaczki@sendit.pl';
        $this->operatorName = 'INPOST';
        $this->destinationCode = 'KRA010';

        $this->orderMock = $this->getMockBuilder(Mage_Sales_Model_Order::class)
                                     ->disableOriginalConstructor()
                                     ->disableOriginalClone()
                                     ->disableArgumentCloning()
                                     ->disallowMockingUnknownTypes()
                                     ->setMethods(
                                        array(
                                            'getShippingMethod',
                                            'getShippingAddress'
                                        )
                                    )
                                     ->getMock();

        $this->observerMock = $this->getMockBuilder(Varien_Event_Observer::class)
                                     ->disableOriginalConstructor()
                                     ->disableOriginalClone()
                                     ->disableArgumentCloning()
                                     ->disallowMockingUnknownTypes()
                                     ->setMethods(array('getEvent'))
                                     ->getMock();
        $this->addressMock = $this->getMockBuilder(Mage_Sales_Model_Order_Address::class)
                                    ->disableOriginalConstructor()
                                    ->disableOriginalClone()
                                    ->disableArgumentCloning()
                                    ->disallowMockingUnknownTypes()
                                    ->setMethods(
                                        array(
                                            'getFirstname',
                                            'getLastname',
                                            'getTelephone',
                                            'getEmail',
                                            'getPosOperator',
                                            'getPosCode'
                                        )
                                    )
                                    ->getMock();

        $this->addressMock->method('getFirstname')->will($this->returnValue($this->receiverFirstName));
        $this->addressMock->method('getLastname')->will($this->returnValue($this->receiverLastName));
        $this->addressMock->method('getTelephone')->will($this->returnValue($this->receiverPhoneNumber));
        $this->addressMock->method('getEmail')->will($this->returnValue($this->receiverEmail));
        $this->addressMock->method('getPosOperator')->will($this->returnValue($this->operatorName));
        $this->addressMock->method('getPosCode')->will($this->returnValue($this->destinationCode));
    }

    public function testCreateOrderViaApi()
    {
        spl_autoload_register(array(Sendit_Bliskapaczka_Model_Observer::class, 'load'), true, true);

        $event = new Varien_Event(array('order' => $this->orderMock));
        $shippingMethod = new Varien_Object(
            array('method' => 'bliskapaczka_sendit_bliskapaczka', 'carrier_code', 'sendit')
        );

        $this->observerMock->expects($this->once())->method('getEvent')->will($this->returnValue($event));

        $this
            ->orderMock
            ->expects($this->any())
            ->method('getShippingMethod')
            ->will($this->returnValue($shippingMethod));

        $this
            ->orderMock
            ->expects($this->any())
            ->method('getShippingAddress')
            ->will($this->returnValue($this->addressMock));

        $observer = new Sendit_Bliskapaczka_Model_Observer();
        $observer->createOrderViaApi($this->observerMock);
    }

    public function testValidateAdminConfiguration()
    {
    // spl_autoload_register(array(Sendit_Bliskapaczka_Model_Observer::class, 'load'), true, true);

    // $observer = new Sendit_Bliskapaczka_Model_Observer();
    // $observer->validateAdminConfiguration();
    }
}
