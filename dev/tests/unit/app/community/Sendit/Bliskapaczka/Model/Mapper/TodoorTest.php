<?php

require_once $GLOBALS['APP_DIR'] . '/code/community/Sendit/Bliskapaczka/Model/Mapper/Abstract.php';
require_once $GLOBALS['APP_DIR'] . '/code/community/Sendit/Bliskapaczka/Model/Mapper/Todoor.php';

use PHPUnit\Framework\TestCase;

class MapperTodoorTest extends TestCase
{

    protected function setUp()
    {
        $this->receiverFirstName = 'Zenek';
        $this->receiverLastName = 'Bliskopaczki';
        $this->receiverPhoneNumber = '504 445 665';
        $this->receiverEmail = 'zenek.bliskopaczki@sendit.pl';
        $this->operatorName = 'DPD';
        $this->receiverStreet = 'Ulica Ulicowa';
        $this->receiverBuildingNumber = '11/123';
        $this->receiverFlatNumber = '';
        $this->receiverPostCode = '12-345';
        $this->receiverCity = 'Mistowe';
        $this->grandTotals = '110.0000';

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
                                            'getStreet',
                                            'getPostcode',
                                            'getCity'
                                        )
                                    )
                                    ->getMock();

        $this->addressMock->method('getFirstname')->will($this->returnValue($this->receiverFirstName));
        $this->addressMock->method('getLastname')->will($this->returnValue($this->receiverLastName));
        $this->addressMock->method('getTelephone')->will($this->returnValue($this->receiverPhoneNumber));
        $this->addressMock->method('getEmail')->will($this->returnValue($this->receiverEmail));
        $this->addressMock->method('getPosOperator')->will($this->returnValue($this->operatorName));
        $this->addressMock->method('getStreet')->will($this->returnValue(
            array(0 => $this->receiverStreet . ' ' . $this->receiverBuildingNumber))
        );
        $this->addressMock->method('getPostcode')->will($this->returnValue($this->receiverPostCode));
        $this->addressMock->method('getCity')->will($this->returnValue($this->receiverCity));

        $shippingMethod = $this->getMockBuilder(Varien_Object::class)
                                 ->disableOriginalConstructor()
                                 ->disableOriginalClone()
                                 ->disableArgumentCloning()
                                 ->disallowMockingUnknownTypes()
                                 ->setMethods(
                                     array(
                                         'getMethod'
                                     )
                                 )
                                 ->getMock();
        $shippingMethod->method('getMethod')->will($this->returnValue('bliskapaczka_courier_DPD'));

        $this->orderMock = $this->getMockBuilder(Mage_Sales_Model_Order::class)
                                     ->disableOriginalConstructor()
                                     ->disableOriginalClone()
                                     ->disableArgumentCloning()
                                     ->disallowMockingUnknownTypes()
                                     ->setMethods(
                                        array(
                                            'getShippingAddress',
                                            'getShippingMethod',
                                            'getGrandTotal'
                                        )
                                    )
                                     ->getMock();

        $this->orderMock->method('getShippingAddress')->will($this->returnValue($this->addressMock));
        $this->orderMock
            ->method('getShippingMethod')
            ->with($this->equalTo(true))
            ->will($this->returnValue($shippingMethod));
        $this->orderMock->method('getGrandTotal')->will($this->returnValue($this->grandTotals));

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
        $this->assertTrue(class_exists('Sendit_Bliskapaczka_Model_Mapper_Todoor'));
    }

    public function testClassHasMethods()
    {
        $this->assertTrue(method_exists('Sendit_Bliskapaczka_Model_Mapper_Todoor', 'getData'));
    }

    public function testTypeOfReturnedData()
    {
        $mapper = new Sendit_Bliskapaczka_Model_Mapper_Todoor();
        $data = $mapper->getData($this->orderMock, $this->helperMock);

        $this->assertTrue(is_array($data));
    }

    public function testMapperForReceiverFirstName()
    {
        $mapper = new Sendit_Bliskapaczka_Model_Mapper_Todoor();
        $data = $mapper->getData($this->orderMock, $this->helperMock);

        $this->assertEquals($this->receiverFirstName, $data['receiverFirstName']);
    }

    public function testMapperForReceiverLastName()
    {
        $mapper = new Sendit_Bliskapaczka_Model_Mapper_Todoor();
        $data = $mapper->getData($this->orderMock, $this->helperMock);

        $this->assertEquals($this->receiverLastName, $data['receiverLastName']);
    }

    public function testMapperForReceiverPhoneNumber()
    {
        $mapper = new Sendit_Bliskapaczka_Model_Mapper_Todoor();
        $data = $mapper->getData($this->orderMock, $this->helperMock);

        $this->assertEquals('504445665', $data['receiverPhoneNumber']);
    }

    public function testMapperForReceiverEmail()
    {
        $mapper = new Sendit_Bliskapaczka_Model_Mapper_Todoor();
        $data = $mapper->getData($this->orderMock, $this->helperMock);

        $this->assertEquals($this->receiverEmail, $data['receiverEmail']);
    }

    public function testMapperForReceiverStreet()
    {
        $mapper = new Sendit_Bliskapaczka_Model_Mapper_Todoor();
        $data = $mapper->getData($this->orderMock, $this->helperMock);

        $this->assertEquals($this->receiverStreet, $data['receiverStreet']);
    }

    public function testMapperForReceiverBuildingNumber()
    {
        $mapper = new Sendit_Bliskapaczka_Model_Mapper_Todoor();
        $data = $mapper->getData($this->orderMock, $this->helperMock);

        $this->assertEquals($this->receiverBuildingNumber, $data['receiverBuildingNumber']);
    }

    public function testMapperForReceiverFlatNumber()
    {
        $mapper = new Sendit_Bliskapaczka_Model_Mapper_Todoor();
        $data = $mapper->getData($this->orderMock, $this->helperMock);

        $this->assertEquals($this->receiverFlatNumber, $data['receiverFlatNumber']);
    }

    public function testMapperForReceiverPostCode()
    {
        $mapper = new Sendit_Bliskapaczka_Model_Mapper_Todoor();
        $data = $mapper->getData($this->orderMock, $this->helperMock);

        $this->assertEquals($this->receiverPostCode, $data['receiverPostCode']);
    }

    public function testMapperForReceiverCity()
    {
        $mapper = new Sendit_Bliskapaczka_Model_Mapper_Todoor();
        $data = $mapper->getData($this->orderMock, $this->helperMock);

        $this->assertEquals($this->receiverCity, $data['receiverCity']);
    }

    public function testMapperForOperatorName()
    {
        $mapper = new Sendit_Bliskapaczka_Model_Mapper_Todoor();
        $data = $mapper->getData($this->orderMock, $this->helperMock);

        $this->assertEquals($this->operatorName, $data['operatorName']);
    }

    public function testMapperForDestinationCode()
    {
        $mapper = new Sendit_Bliskapaczka_Model_Mapper_Todoor();
        $data = $mapper->getData($this->orderMock, $this->helperMock);

        $this->assertEquals($this->destinationCode, $data['destinationCode']);
    }

    public function testMapperForParcel()
    {
        $mapper = new Sendit_Bliskapaczka_Model_Mapper_Todoor();
        $data = $mapper->getData($this->orderMock, $this->helperMock);

        $this->assertTrue(is_array($data['parcel']));
    }

    public function testMapperForAdditionalInformation()
    {
        $mapper = new Sendit_Bliskapaczka_Model_Mapper_Todoor();
        $data = $mapper->getData($this->orderMock, $this->helperMock);

        $this->assertEquals($this->incrementId, $data['additionalInformation']);
    }

    public function testMapperForReference()
    {
        $mapper = new Sendit_Bliskapaczka_Model_Mapper_Todoor();

        $data = $mapper->getData($this->orderMock, $this->helperMock, true);
        $this->assertEquals($this->incrementId, $data['reference']);

        $data = $mapper->getData($this->orderMock, $this->helperMock, false);
        $this->assertEquals(null, $data['reference']);
    }

    public function testDeliveryType()
    {
        $mapper = new Sendit_Bliskapaczka_Model_Mapper_Todoor();

        $data = $mapper->getData($this->orderMock, $this->helperMock, true);
        $this->assertEquals('D2D', $data['deliveryType']);
    }

    public function testCoD()
    {
        # Without CoD
        $mapper = new Sendit_Bliskapaczka_Model_Mapper_Todoor();

        $data = $mapper->getData($this->orderMock, $this->helperMock, true);
        $this->assertEquals(null, $data['codValue']);
        $this->assertEquals(null, $data['insuranceValue']);

        # With CoD
        $shippingMethod = $this->getMockBuilder(Varien_Object::class)
                                 ->disableOriginalConstructor()
                                 ->disableOriginalClone()
                                 ->disableArgumentCloning()
                                 ->disallowMockingUnknownTypes()
                                 ->setMethods(
                                     array(
                                         'getMethod'
                                     )
                                 )
                                 ->getMock();
        $shippingMethod->method('getMethod')->will($this->returnValue('bliskapaczka_courier_DPD_COD'));

        $addressMock = $this->getMockBuilder(Mage_Sales_Model_Order_Address::class)
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

        $addressMock->method('getFirstname')->will($this->returnValue($this->receiverFirstName));
        $addressMock->method('getLastname')->will($this->returnValue($this->receiverLastName));
        $addressMock->method('getTelephone')->will($this->returnValue($this->receiverPhoneNumber));
        $addressMock->method('getEmail')->will($this->returnValue($this->receiverEmail));
        $addressMock->method('getPosOperator')->will($this->returnValue('DPD_COD'));
        $addressMock->method('getPosCode')->will($this->returnValue($this->destinationCode));

        $orderMockFirst = $this->getMockBuilder(Mage_Sales_Model_Order::class)
                                     ->disableOriginalConstructor()
                                     ->disableOriginalClone()
                                     ->disableArgumentCloning()
                                     ->disallowMockingUnknownTypes()
                                     ->setMethods(
                                         array(
                                             'getShippingAddress',
                                             'getIncrementId',
                                             'getGrandTotal',
                                             'getShippingMethod'
                                         )
                                     )
                                     ->getMock();

        $orderMockFirst->method('getShippingAddress')->will($this->returnValue($addressMock));
        $orderMockFirst->method('getIncrementId')->will($this->returnValue($this->incrementId));
        $orderMockFirst
            ->method('getShippingMethod')
            ->with($this->equalTo(true))
            ->will($this->returnValue($shippingMethod));

        $orderMockFirst->method('getGrandTotal')->will($this->returnValue('110.0000'));
        $data = $mapper->getData($orderMockFirst, $this->helperMock, true);
        $this->assertSame('110', $data['codValue']);
        $this->assertSame('110', $data['insuranceValue']);

        $orderMockSecound = $this->getMockBuilder(Mage_Sales_Model_Order::class)
                                     ->disableOriginalConstructor()
                                     ->disableOriginalClone()
                                     ->disableArgumentCloning()
                                     ->disallowMockingUnknownTypes()
                                     ->setMethods(
                                         array(
                                             'getShippingAddress',
                                             'getIncrementId',
                                             'getGrandTotal',
                                             'getShippingMethod'
                                         )
                                     )
                                     ->getMock();

        $orderMockSecound->method('getShippingAddress')->will($this->returnValue($addressMock));
        $orderMockSecound->method('getIncrementId')->will($this->returnValue($this->incrementId));
        $orderMockSecound
            ->method('getShippingMethod')
            ->with($this->equalTo(true))
            ->will($this->returnValue($shippingMethod));

        $orderMockSecound->method('getGrandTotal')->will($this->returnValue('110.0100'));
        $data = $mapper->getData($orderMockSecound, $this->helperMock, true);
        $this->assertSame('110.01', $data['codValue']);
        $this->assertSame('110.01', $data['insuranceValue']);
    }
}
