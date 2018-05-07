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
}
