<?php

require $GLOBALS['APP_DIR'] . '/code/community/Sendit/Bliskapaczka/Model/Order.php';

use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase
{
    public function testClassExists()
    {
        $this->assertTrue(class_exists('Sendit_Bliskapaczka_Model_Order'));
    }

    public function testClassHasMethods()
    {
        $this->assertTrue(method_exists('Sendit_Bliskapaczka_Model_Order', 'canRetry'));
        $this->assertTrue(method_exists('Sendit_Bliskapaczka_Model_Order', 'retry'));
    }

    public function testCanRetry() {
        $mock = $this->getMockBuilder(Sendit_Bliskapaczka_Model_Order::class)
                        ->setMethods(array('getNumber', 'getStatus', 'getErrorReason'))
                        ->getMock();

        $mock->expects($this->once())
             ->method('getNumber')
             ->will($this->returnValue('000000636P-000000352'));
        $mock->expects($this->once())
             ->method('getStatus')
             ->will($this->returnValue('ERROR'));
        $mock->expects($this->once())
             ->method('getErrorReason')
             ->will($this->returnValue('GENERIC_ADVICE_ERROR'));

        $this->assertEquals(true, $mock->canRetry());

        $mock = $this->getMockBuilder(Sendit_Bliskapaczka_Model_Order::class)
                        ->setMethods(array('getNumber', 'getStatus', 'getErrorReason'))
                        ->getMock();

        $mock->expects($this->once())
             ->method('getNumber')
             ->will($this->returnValue('000000636P-000000352'));
        $mock->expects($this->once())
             ->method('getStatus')
             ->will($this->returnValue('ERROR'));
        $mock->expects($this->once())
             ->method('getErrorReason')
             ->will($this->returnValue('AUTHORIZATION_ERROR'));

        $this->assertEquals(true, $mock->canRetry());

        $mock = $this->getMockBuilder(Sendit_Bliskapaczka_Model_Order::class)
                        ->setMethods(array('getNumber', 'getStatus', 'getErrorReason'))
                        ->getMock();

        $mock->expects($this->once())
             ->method('getNumber')
             ->will($this->returnValue('000000636P-000000352'));
        $mock->expects($this->once())
             ->method('getStatus')
             ->will($this->returnValue('ERROR'));
        $mock->expects($this->once())
             ->method('getErrorReason')
             ->will($this->returnValue('LABEL_GENERATION_ERROR'));

        $this->assertEquals(true, $mock->canRetry());

        $mock = $this->getMockBuilder(Sendit_Bliskapaczka_Model_Order::class)
                        ->setMethods(array('getNumber', 'getStatus', 'getErrorReason'))
                        ->getMock();

        $mock->expects($this->once())
             ->method('getNumber')
             ->will($this->returnValue('000000636P-000000352'));
        $mock->expects($this->once())
             ->method('getStatus')
             ->will($this->returnValue('ERROR'));
        $mock->expects($this->once())
             ->method('getErrorReason')
             ->will($this->returnValue('WAYBILL_PROCESS_ERROR'));

        $this->assertEquals(true, $mock->canRetry());

        $mock = $this->getMockBuilder(Sendit_Bliskapaczka_Model_Order::class)
                        ->setMethods(array('getNumber', 'getStatus', 'getErrorReason'))
                        ->getMock();

        $mock->expects($this->once())
             ->method('getNumber')
             ->will($this->returnValue('000000636P-000000352'));
        $mock->expects($this->once())
             ->method('getStatus')
             ->will($this->returnValue('ERROR'));
        $mock->expects($this->once())
             ->method('getErrorReason')
             ->will($this->returnValue('BACKEND_ERROR'));

        $this->assertEquals(true, $mock->canRetry());

        $mock = $this->getMockBuilder(Sendit_Bliskapaczka_Model_Order::class)
                        ->setMethods(array('getNumber', 'getStatus', 'getErrorReason'))
                        ->getMock();

        $mock->expects($this->once())
             ->method('getNumber')
             ->will($this->returnValue('000000636P-000000352'));
        $mock->expects($this->once())
             ->method('getStatus')
             ->will($this->returnValue('ERROR'));
        $mock->expects($this->once())
             ->method('getErrorReason')
             ->will($this->returnValue('some_other_reason'));

        $this->assertEquals(false, $mock->canRetry());

        $mock = $this->getMockBuilder(Sendit_Bliskapaczka_Model_Order::class)
                        ->setMethods(array('getNumber', 'getStatus', 'getErrorReason'))
                        ->getMock();

        $mock->expects($this->once())
             ->method('getNumber')
             ->will($this->returnValue('000000636P-000000352'));
        $mock->expects($this->once())
             ->method('getStatus')
             ->will($this->returnValue('some_error'));

        $this->assertEquals(false, $mock->canRetry());
    }
}
