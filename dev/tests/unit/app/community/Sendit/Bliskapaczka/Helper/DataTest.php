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
     * @dataProvider phpneNumbers
     */
    public function testCleaningPhoneNumber($phoneNumber)
    {
        $helper = new Sendit_Bliskapaczka_Helper_Data();
     
        $this->assertEquals('606606606', $helper->telephoneNumberCleaning($phoneNumber));
    }

    public function phpneNumbers()
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
