<?php

require $GLOBALS['APP_DIR'] . '/code/community/Sendit/Bliskapaczka/Helper/Data.php';

use PHPUnit\Framework\TestCase;

class MockRateInpost {
    public function getCode() { return 'sendit_bliskapaczka_INPOST'; }
    public function getPrice() { return 10.27; }
}

class MockRateRuch {
    public function getCode() { return 'sendit_bliskapaczka_RUCH'; }
    public function getPrice() { return 10.27; }
}

class MockRatePoczta {
    public function getCode() { return 'sendit_bliskapaczka_POCZTA'; }
    public function getPrice() { return 8.99; }
};

class MockRatePocztaFreeShipping {
    public function getCode() { return 'sendit_bliskapaczka_POCZTA'; }
    public function getPrice() { return 0.00; }
};

class DataTest extends TestCase
{
    public function testClassExists()
    {
        $this->assertTrue(class_exists('Sendit_Bliskapaczka_Helper_Data'));
    }

    public function testClassHasMethods()
    {
        $this->assertTrue(method_exists('Sendit_Bliskapaczka_Helper_Data', 'getParcelDimensions'));
        $this->assertTrue(method_exists('Sendit_Bliskapaczka_Helper_Data', 'getLowestPrice'));
        $this->assertTrue(method_exists('Sendit_Bliskapaczka_Helper_Data', 'getPriceForCarrier'));
    }

    public function testClassExtendMageCoreHelperData()
    {
        $helper = new Sendit_Bliskapaczka_Helper_Data();
        $this->assertTrue($helper instanceof Mage_Core_Helper_Data);
    }

    public function testConstants()
    {
        $helper = new Sendit_Bliskapaczka_Helper_Data();

        $this->assertEquals('carriers/sendit_bliskapaczka/parcel_size_type', $helper::PARCEL_SIZE_TYPE_XML_PATH);
        $this->assertEquals(
            'carriers/sendit_bliskapaczka/parcel_size_type_fixed_size_x',
            $helper::PARCEL_TYPE_FIXED_SIZE_X_XML_PATH
        );
        $this->assertEquals(
            'carriers/sendit_bliskapaczka/parcel_size_type_fixed_size_y',
            $helper::PARCEL_TYPE_FIXED_SIZE_Y_XML_PATH
        );
        $this->assertEquals(
            'carriers/sendit_bliskapaczka/parcel_size_type_fixed_size_z',
            $helper::PARCEL_TYPE_FIXED_SIZE_Z_XML_PATH
        );
        $this->assertEquals(
            'carriers/sendit_bliskapaczka/parcel_size_type_fixed_size_weight',
            $helper::PARCEL_TYPE_FIXED_SIZE_WEIGHT_XML_PATH
        );

        $this->assertEquals(
            'carriers/sendit_bliskapaczka/bliskapaczkaapikey',
            $helper::API_KEY_XML_PATH
        );
        $this->assertEquals(
            'carriers/sendit_bliskapaczka/test_mode',
            $helper::API_TEST_MODE_XML_PATH
        );

        $this->assertEquals(
            'carriers/sendit_bliskapaczka/sender_email',
            $helper::SENDER_EMAIL
        );
        $this->assertEquals(
            'carriers/sendit_bliskapaczka/sender_first_name',
            $helper::SENDER_FIRST_NAME
        );
        $this->assertEquals(
            'carriers/sendit_bliskapaczka/sender_last_name',
            $helper::SENDER_LAST_NAME
        );
        $this->assertEquals(
            'carriers/sendit_bliskapaczka/sender_phone_number',
            $helper::SENDER_PHONE_NUMBER
        );
        $this->assertEquals(
            'carriers/sendit_bliskapaczka/sender_street',
            $helper::SENDER_STREET
        );
        $this->assertEquals(
            'carriers/sendit_bliskapaczka/sender_building_number',
            $helper::SENDER_BUILDING_NUMBER
        );
        $this->assertEquals(
            'carriers/sendit_bliskapaczka/sender_flat_number',
            $helper::SENDER_FLAT_NUMBER
        );
        $this->assertEquals(
            'carriers/sendit_bliskapaczka/sender_post_code',
            $helper::SENDER_POST_CODE
        );
        $this->assertEquals(
            'carriers/sendit_bliskapaczka/sender_city',
            $helper::SENDER_CITY
        );
        $this->assertEquals(
            'carriers/sendit_bliskapaczka/google_map_api_key',
            $helper::GOOGLE_MAP_API_KEY_XML_PATH
        );
    }

    /**
     * @dataProvider dimensions
     */
    public function testGetParcelDimensions($type, $height, $length, $width, $weight)
    {
        $helper = $this->getMockBuilder(Sendit_Bliskapaczka_Helper_Data::class)
            ->setMethods(array('getStoreConfigWrapper'))
            ->getMock();

        if ($type == 'fixed') {
            $map = array(
                array('carriers/sendit_bliskapaczka/parcel_size_type', 'fixed'),
                array('carriers/sendit_bliskapaczka/parcel_size_type_fixed_size_x', $height),
                array('carriers/sendit_bliskapaczka/parcel_size_type_fixed_size_y', $length),
                array('carriers/sendit_bliskapaczka/parcel_size_type_fixed_size_z', $width),
                array('carriers/sendit_bliskapaczka/parcel_size_type_fixed_size_weight', $weight)
            );

            $helper->method('getStoreConfigWrapper')->will($this->returnValueMap($map));
        }

        $dimensions = $helper->getParcelDimensions($type);

        $this->assertEquals($height, $dimensions["height"]);
        $this->assertEquals($length, $dimensions["length"]);
        $this->assertEquals($width, $dimensions["width"]);
        $this->assertEquals($weight, $dimensions["weight"]);
    }

    public function dimensions()
    {
        return [
            ['fixed', 14, 14, 16, 1],
            [
                'default',
                Sendit_Bliskapaczka_Helper_Data::PARCEL_DEFAULT_SIZE_X,
                Sendit_Bliskapaczka_Helper_Data::PARCEL_DEFAULT_SIZE_Y,
                Sendit_Bliskapaczka_Helper_Data::PARCEL_DEFAULT_SIZE_Z,
                Sendit_Bliskapaczka_Helper_Data::PARCEL_DEFAULT_SIZE_WEIGHT
            ],
        ];
    }

    public function testGetLowestPrice()
    {
        $priceListEachOther = '[
            {
                "operatorName":"INPOST",
                "availabilityStatus":true,
                "price":{"net":8.35,"vat":1.92,"gross":10.27},
                "unavailabilityReason":null
            },
            {
                "operatorName":"RUCH",
                "availabilityStatus":true,
                "price":{"net":4.87,"vat":1.12,"gross":5.99},
                "unavailabilityReason":null
            },
            {
                "operatorName":"POCZTA",
                "availabilityStatus":true,
                "price":{"net":7.31,"vat":1.68,"gross":8.99},
                "unavailabilityReason":null
            }]';

        $priceListOneTheSame = '[
            {
                "operatorName":"INPOST",
                "availabilityStatus":true,
                "price":{"net":8.35,"vat":1.92,"gross":10.27},
                "unavailabilityReason":null
            },
            {
                "operatorName":"RUCH",
                "availabilityStatus":true,
                "price":{"net":4.87,"vat":1.12,"gross":10.27},
                "unavailabilityReason":null
            },
            {
                "operatorName":"POCZTA",
                "availabilityStatus":true,
                "price":{"net":7.31,"vat":1.68,"gross":8.99},
                "unavailabilityReason":null
            }]';
        $priceListOnlyOne = '[
            {
                "operatorName":"INPOST",
                "availabilityStatus":true,
                "price":{"net":8.35,"vat":1.92,"gross":10.27},
                "unavailabilityReason":null
            },
            {
                "operatorName":"RUCH",
                "availabilityStatus":true,
                "price":{"net":4.87,"vat":1.12,"gross":10.27},
                "unavailabilityReason":null
            },
            {
                "operatorName":"POCZTA",
                "availabilityStatus":false,
                "price":null,
                "unavailabilityReason": {
                    "errors": {
                        "messageCode": "ppo.api.error.pricing.algorithm.constraints.dimensionsTooSmall",
                        "message": "Allowed parcel dimensions too small. Min dimensions: 16x10x1 cm",
                        "field": null,
                        "value": null
                    }
                }
            }]';

        $helper = new Sendit_Bliskapaczka_Helper_Data();

        $lowestPrice = $helper->getLowestPrice(
            json_decode($priceListEachOther),
            array(new MockRateInpost(), new MockRatePoczta(), new MockRateRuch())
        );
        $this->assertEquals(5.99, $lowestPrice);

        $lowestPrice = $helper->getLowestPrice(
            json_decode($priceListOneTheSame),
            array(new MockRateInpost(), new MockRatePoczta(), new MockRateRuch())
        );
        $this->assertEquals(8.99, $lowestPrice);

        $lowestPrice = $helper->getLowestPrice(
            json_decode($priceListOnlyOne),
            array(new MockRateInpost(), new MockRatePoczta(), new MockRateRuch())
        );
        $this->assertEquals(10.27, $lowestPrice);

        $lowestPrice = $helper->getLowestPrice(
            json_decode($priceListOneTheSame),
            array(new MockRateInpost(), new MockRatePocztaFreeShipping(), new MockRateRuch())
        );
        $this->assertEquals(0.00, $lowestPrice);
    }

    public function testGetPriceForCarrier()
    {
        $priceList = '[
            {
                "operatorName":"INPOST",
                "availabilityStatus":true,
                "price":{"net":8.35,"vat":1.92,"gross":10.27},
                "unavailabilityReason":null
            },
            {
                "operatorName":"RUCH",
                "availabilityStatus":true,
                "price":{"net":4.87,"vat":1.12,"gross":5.99},
                "unavailabilityReason":null
            },
            {
                "operatorName":"POCZTA",
                "availabilityStatus":true,
                "price":{"net":7.31,"vat":1.68,"gross":8.99},
                "unavailabilityReason":null
            }]';
        $helper = new Sendit_Bliskapaczka_Helper_Data();

        $price = $helper->getPriceForCarrier(
            json_decode($priceList),
            array(new MockRateInpost(), new MockRateRuch(), new MockRatePoczta()),
            'INPOST'
        );
        $this->assertEquals(10.27, $price);

        $price = $helper->getPriceForCarrier(
            json_decode($priceList),
            array(new MockRateInpost(), new MockRateRuch(), new MockRatePoczta()),
            'RUCH'
        );
        $this->assertEquals(5.99, $price);

        $price = $helper->getPriceForCarrier(
            json_decode($priceList),
            array(new MockRateInpost(), new MockRateRuch(), new MockRatePoczta()),
            'POCZTA'
        );
        $this->assertEquals(8.99, $price);

        $price = $helper->getPriceForCarrier(
            json_decode($priceList),
            array(new MockRateInpost(), new MockRateRuch(), new MockRatePoczta()),
            'POCZTA'
        );
        $this->assertEquals(8.99, $price);
    }

    /**
     * @dataProvider pricings
     */
    public function testGetPriceList($pricing, $type, $dimensions, $expectedValue)
    {
        $helper = $this->getMockBuilder(Sendit_Bliskapaczka_Helper_Data::class)
            ->setMethods(
                array(
                    '_getPricing',
                    'getApiClientPricing',
                    'getParcelDimensions'
                )
            )
            ->getMock();

        $helper->method('_getPricing')->willReturn($this->getPricing());

        $apiClientOrder = $this->getApiClientPricing();
        $apiClientOrder->method('get')->will($this->returnValue($pricing));
        $helper->method('getApiClientPricing')->willReturn($apiClientOrder);

        $helper->expects($this->exactly(2))
             ->method('getParcelDimensions')
             ->with($type)
             ->will($this->returnValue($dimensions));

        $pricing = $helper->getPriceList(null, $type);
        if (empty($expectedValue)) {
            $this->assertEquals($expectedValue, $pricing);
        } else {
            $this->assertTrue(is_array($pricing));
            $this->assertEquals($expectedValue->price->gross, $price->price->gross);
        }
    }

    protected function getPricing()
    {
        $dpd = new StdClass();
        $dpd->operatorName = 'DPD';
        $dpd->operatorFullName = 'DPD';

        $ruch = new StdClass();
        $ruch->operatorName = 'RUCH';
        $ruch->operatorFullName = 'Ruch';

        $poczta = new StdClass();
        $poczta->operatorName = 'POCZTA';
        $poczta->operatorFullName ='Poczta Polska';

        $inpost = new StdClass();
        $inpost->operatorName = 'INPOST';
        $inpost->operatorFullName ='Inpost';

        return array(
            $dpd,
            $ruch,
            $poczta,
            $inpost
        );
    }

    protected function getApiClientPricing()
    {
        $apiClientOrder = $this->getMockBuilder(\Bliskapaczka\ApiClient\Bliskapaczka\Pricing::class)
                                     ->disableOriginalConstructor()
                                     ->disableOriginalClone()
                                     ->disableArgumentCloning()
                                     ->disallowMockingUnknownTypes()
                                     ->setMethods(array('get'))
                                     ->getMock();

        return $apiClientOrder;
    }

    public function pricings()
    {
        $defaultDimensions = array(
            "height" => 12,
            "length" => 12,
            "width" => 16,
            "weight" => 1
        );

        $price = new StdClass();
        $price->gross = 5.99;
        $price->net = 4.87;
        $price->vat = 1.12;

        $dpd = new StdClass();
        $dpd->availabilityStatus = true;
        $dpd->operatorName = 'DPD';
        $dpd->operatorFullName = 'DPD';
        $operator->price = $price;

        return [
            ['', 'fixed', $defaultDimensions, []],
            ['[]', 'fixed', $defaultDimensions, []],
            [
                '[{"availabilityStatus":false, "operatorName":"DPD", "price":{"net":4.87,"vat":1.12,"gross":5.99}}]',
                'fixed',
                $defaultDimensions,
                []
            ],
            [
                '[{"availabilityStatus":true, "operatorName":"DPD", "price":{"net":4.87,"vat":1.12,"gross":5.99}}]',
                'fixed',
                $defaultDimensions,
                [$dpd]
            ],
            [
                '[{"availabilityStatus":true, "operatorName":"DPD", "price":{"net":4.87,"vat":1.12,"gross":5.99}}]',
                'fixed',
                $defaultDimensions,
                [$dpd]
            ]

        ];
    }

    public function testGetOperatorsForWidget()
    {
        $priceList = '[
            {
                "operatorName":"INPOST",
                "availabilityStatus":true,
                "price":{"net":8.35,"vat":1.92,"gross":10.27},
                "unavailabilityReason":null
            },
            {
                "operatorName":"RUCH",
                "availabilityStatus":true,
                "price":{"net":4.87,"vat":1.12,"gross":5.99},
                "unavailabilityReason":null
            },
            {
                "operatorName":"POCZTA",
                "availabilityStatus":false,
                "price":null,
                "unavailabilityReason": {
                    "errors": {
                        "messageCode": "ppo.api.error.pricing.algorithm.constraints.dimensionsTooSmall",
                        "message": "Allowed parcel dimensions too small. Min dimensions: 16x10x1 cm",
                        "field": null,
                        "value": null
                    }
                }
            }]';

        $helper = new Sendit_Bliskapaczka_Helper_Data();
        $this->assertEquals(
            '[{"operator":"INPOST","price":10.27},{"operator":"RUCH","price":5.99}]',
            $helper->getOperatorsForWidget(
                array(new MockRateInpost(), new MockRateRuch(), new MockRatePoczta()),
                json_decode($priceList),
                false
            )
        );
    }

    /**
     * @dataProvider phoneNumbers
     */
    public function testCleaningPhoneNumber($phoneNumber)
    {
        $helper = new Sendit_Bliskapaczka_Helper_Data();
     
        $this->assertEquals('606606606', $helper->telephoneNumberCleaning($phoneNumber));
    }

    public function phoneNumbers()
    {
        return [
            ['606-606-606'],
            ['606 606 606'],
            ['+48 606 606 606'],
            ['+48606606606'],
            ['+48 606-606-606'],
            ['+48-606-606-606']
        ];
    }

    public function testGetApiMode()
    {
        $helper = new Sendit_Bliskapaczka_Helper_Data();

        $mode = $helper->getApiMode(1);
        $this->assertEquals('test', $mode);

        $mode = $helper->getApiMode(0);
        $this->assertEquals('prod', $mode);

        $mode = $helper->getApiMode();
        $this->assertEquals('prod', $mode);
    }

    /**
     * @dataProvider courierShippingMethods
     */
    public function testIsCourier($method)
    {
        $helper = new Sendit_Bliskapaczka_Helper_Data();
        $this->assertTrue($helper->isCourier($method));
    }

    public function courierShippingMethods()
    {
        return [
            ['bliskapaczka_courier_sendit_bliskapaczka_courier'],
            ['bliskapaczka_courier_DPD']
        ];
    }

    /**
     * @dataProvider pointShippingMethods
     */
    public function testIsPoint($method)
    {
        $helper = new Sendit_Bliskapaczka_Helper_Data();
        $this->assertTrue($helper->isPoint($method));
    }

    public function pointShippingMethods()
    {
        return [
            ['bliskapaczka_sendit_bliskapaczka'],
            ['bliskapaczka_sendit_bliskapaczka_COD']
        ];
    }
}
