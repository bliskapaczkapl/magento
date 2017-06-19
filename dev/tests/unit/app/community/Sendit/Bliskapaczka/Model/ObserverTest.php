<?php

require $GLOBALS['APP_DIR'] . '/code/community/Sendit/Bliskapaczka/Model/Observer.php';

use PHPUnit\Framework\TestCase;

class ObserverTest extends TestCase
{

    protected function setUp()
    {
        $this->orderMock = $this->getMockBuilder(Mage_Sales_Model_Order::class)
                                     ->disableOriginalConstructor()
                                     ->disableOriginalClone()
                                     ->disableArgumentCloning()
                                     ->disallowMockingUnknownTypes()
                                     ->setMethods(array('getShippingMethod'))
                                     ->getMock();

        $this->observerMock = $this->getMockBuilder(Varien_Event_Observer::class)
                                     ->disableOriginalConstructor()
                                     ->disableOriginalClone()
                                     ->disableArgumentCloning()
                                     ->disallowMockingUnknownTypes()
                                     ->setMethods(array('getEvent'))
                                     ->getMock();
    }

    public function testClassExists()
    {
        $this->assertTrue(class_exists('Sendit_Bliskapaczka_Model_Observer'));
    }

    public function testClassHasMethods()
    {
        $this->assertTrue(method_exists('Sendit_Bliskapaczka_Model_Observer', 'setPosData'));
        $this->assertTrue(method_exists('Sendit_Bliskapaczka_Model_Observer', 'createOrderViaApi'));
    }

    // public function testCreateOrderViaApi()
    // {
    //     spl_autoload_register(array(Sendit_Bliskapaczka_Model_Observer::class, 'load'), true, true);

    //     $event = new Varien_Event(array('order' => $this->orderMock));
    //     $shippingMethod = new Varien_Object(array('method' => 'bliskapaczka_sendit_bliskapaczka', 'carrier_code', 'sendit'));

    //     $this->observerMock->expects($this->once())->method('getEvent')->will($this->returnValue($event));

    //     $this->orderMock->expects($this->once())->method('getShippingMethod')->will($this->returnValue($shippingMethod));

    //     $observer = new Sendit_Bliskapaczka_Model_Observer();
    //     $observer->createOrderViaApi($this->observerMock);
    // }
}
