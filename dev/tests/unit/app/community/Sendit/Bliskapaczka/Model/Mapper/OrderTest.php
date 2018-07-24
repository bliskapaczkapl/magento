<?php

require_once $GLOBALS['APP_DIR'] . '/code/community/Sendit/Bliskapaczka/Model/Mapper/Abstract.php';
require $GLOBALS['APP_DIR'] . '/code/community/Sendit/Bliskapaczka/Model/Mapper/Order.php';

use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase
{

    protected function setUp()
    {
        $this->receiverFirstName = 'Zenek';
        $this->receiverLastName = 'Bliskopaczki';
        $this->receiverPhoneNumber = '504 445 665';
        $this->receiverEmail = 'zenek.bliskopaczki@sendit.pl';
        $this->operatorName = 'INPOST';
        $this->destinationCode = 'KRA010';

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

        $this->incrementId = '000000000001191';

        $this->orderMock = $this->getMockBuilder(Mage_Sales_Model_Order::class)
                                     ->disableOriginalConstructor()
                                     ->disableOriginalClone()
                                     ->disableArgumentCloning()
                                     ->disallowMockingUnknownTypes()
                                     ->setMethods(
                                        array(
                                            'getShippingAddress',
                                            'getIncrementId'
                                        )
                                    )
                                     ->getMock();

        $this->orderMock->method('getShippingAddress')->will($this->returnValue($this->addressMock));
        $this->orderMock->method('getIncrementId')->will($this->returnValue($this->incrementId));

        $this->helperMock = $this->getMockBuilder(Sendit_Bliskapaczka_Helper_Data::class)
                                     ->disableOriginalConstructor()
                                     ->disableOriginalClone()
                                     ->disableArgumentCloning()
                                     ->disallowMockingUnknownTypes()
                                     ->setMethods(
                                         array(
                                             'getParcelDimensions',
                                             'telephoneNumberCleaning'
                                         )
                                     )
                                     ->getMock();

        $dimensions = array(
            "height" => 12,
            "length" => 12,
            "width" => 12,
            "weight" => 1
        );

        $this->helperMock->method('getParcelDimensions')->will($this->returnValue($dimensions));
        $this->helperMock->method('telephoneNumberCleaning')
            ->with($this->equalTo('504 445 665'))
            ->will($this->returnValue('504445665'));
    }

    public function testClassExists()
    {
        $this->assertTrue(class_exists('Sendit_Bliskapaczka_Model_Mapper_Order'));
    }

    public function testClassHasMethods()
    {
        $this->assertTrue(method_exists('Sendit_Bliskapaczka_Model_Mapper_Order', 'getData'));
    }

    public function testTypeOfReturnedData()
    {
        $mapper = new Sendit_Bliskapaczka_Model_Mapper_Order();
        $data = $mapper->getData($this->orderMock, $this->helperMock);

        $this->assertTrue(is_array($data));
    }

    public function testMapperForReceiverFirstName()
    {
        $mapper = new Sendit_Bliskapaczka_Model_Mapper_Order();
        $data = $mapper->getData($this->orderMock, $this->helperMock);

        $this->assertEquals($this->receiverFirstName, $data['receiverFirstName']);
    }

    public function testMapperForReceiverLastName()
    {
        $mapper = new Sendit_Bliskapaczka_Model_Mapper_Order();
        $data = $mapper->getData($this->orderMock, $this->helperMock);

        $this->assertEquals($this->receiverLastName, $data['receiverLastName']);
    }

    public function testMapperForReceiverPhoneNumber()
    {
        $mapper = new Sendit_Bliskapaczka_Model_Mapper_Order();
        $data = $mapper->getData($this->orderMock, $this->helperMock);

        $this->assertEquals('504445665', $data['receiverPhoneNumber']);
    }

    public function testMapperForReceiverEmail()
    {
        $mapper = new Sendit_Bliskapaczka_Model_Mapper_Order();
        $data = $mapper->getData($this->orderMock, $this->helperMock);

        $this->assertEquals($this->receiverEmail, $data['receiverEmail']);
    }

    public function testMapperForOperatorName()
    {
        $mapper = new Sendit_Bliskapaczka_Model_Mapper_Order();
        $data = $mapper->getData($this->orderMock, $this->helperMock);

        $this->assertEquals($this->operatorName, $data['operatorName']);
    }

    public function testMapperForDestinationCode()
    {
        $mapper = new Sendit_Bliskapaczka_Model_Mapper_Order();
        $data = $mapper->getData($this->orderMock, $this->helperMock);

        $this->assertEquals($this->destinationCode, $data['destinationCode']);
    }

    public function testMapperForParcel()
    {
        $mapper = new Sendit_Bliskapaczka_Model_Mapper_Order();
        $data = $mapper->getData($this->orderMock, $this->helperMock);

        $this->assertTrue(is_array($data['parcel']));
    }


    public function testMapperForAdditionalInformation()
    {
        $mapper = new Sendit_Bliskapaczka_Model_Mapper_Order();
        $data = $mapper->getData($this->orderMock, $this->helperMock);

        $this->assertEquals($this->incrementId, $data['additionalInformation']);
    }
}
