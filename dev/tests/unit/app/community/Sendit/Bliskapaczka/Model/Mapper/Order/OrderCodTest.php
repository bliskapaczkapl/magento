<?php

require_once $GLOBALS['APP_DIR'] . '/code/community/Sendit/Bliskapaczka/Model/Mapper/Abstract.php';
require_once $GLOBALS['APP_DIR'] . '/code/community/Sendit/Bliskapaczka/Model/Mapper/Order.php';

use PHPUnit\Framework\TestCase;

class OrderCodTest extends TestCase
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
        $this->addressMock->method('getPosOperator')->will($this->returnValue($this->operatorName . '_COD'));
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

    public function testMapperForOperatorName()
    {
        $mapper = new Sendit_Bliskapaczka_Model_Mapper_Order();
        $data = $mapper->getData($this->orderMock, $this->helperMock);

        $this->assertEquals($this->operatorName, $data['operatorName']);
    }
}
