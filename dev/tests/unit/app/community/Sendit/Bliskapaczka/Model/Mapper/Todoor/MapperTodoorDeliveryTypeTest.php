<?php

require_once $GLOBALS['APP_DIR'] . '/code/community/Sendit/Bliskapaczka/Model/Mapper/Abstract.php';
require_once $GLOBALS['APP_DIR'] . '/code/community/Sendit/Bliskapaczka/Model/Mapper/Todoor.php';

use PHPUnit\Framework\TestCase;

class MapperTodoorDeliveryTypeTest extends TestCase
{

    protected function setUp()
    {
        $this->receiverFirstName = 'Zenek';
        $this->receiverLastName = 'Bliskopaczki';
        $this->receiverPhoneNumber = '504 445 665';
        $this->receiverEmail = 'zenek.bliskopaczki@sendit.pl';
        $this->operatorName = 'POCZTA_P2D';
        $this->receiverStreet = 'Ulica Ulicowa';
        $this->receiverBuildingNumber = '11/123';
        $this->receiverFlatNumber = '';
        $this->receiverPostCode = '12-345';
        $this->receiverCity = 'Mistowe';

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
        $this->addressMock->method('getPosOperator')->will($this->returnValue($this->operatorName . '_COD'));
        $this->addressMock->method('getStreet')->will($this->returnValue(
            array(0 => $this->receiverStreet . ' ' . $this->receiverBuildingNumber))
        );
        $this->addressMock->method('getPostcode')->will($this->returnValue($this->receiverPostCode));
        $this->addressMock->method('getCity')->will($this->returnValue($this->receiverCity));

        $this->orderMock = $this->getMockBuilder(Mage_Sales_Model_Order::class)
                                     ->disableOriginalConstructor()
                                     ->disableOriginalClone()
                                     ->disableArgumentCloning()
                                     ->disallowMockingUnknownTypes()
                                     ->setMethods(
                                        array(
                                            'getShippingAddress',
                                            'getShippingMethod'
                                        )
                                    )
                                     ->getMock();

        $this->orderMock->method('getShippingAddress')->will($this->returnValue($this->addressMock));
        $this->orderMock->method('getShippingMethod')->will($this->returnValue($shippingMethod));

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

    public function testDeliveryType()
    {
        $mapper = new Sendit_Bliskapaczka_Model_Mapper_Todoor();

        $data = $mapper->getData($this->orderMock, $this->helperMock, true);
        $this->assertEquals('P2D', $data['deliveryType']);
    }
}
