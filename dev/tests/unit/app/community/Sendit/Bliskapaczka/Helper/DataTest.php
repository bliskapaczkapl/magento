<?php

require $GLOBALS['APP_DIR'] . '/code/community/Sendit/Bliskapaczka/Helper/Data.php';

use PHPUnit\Framework\TestCase;

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
        $hepler = new Sendit_Bliskapaczka_Helper_Data();
        $this->assertTrue($hepler instanceof Mage_Core_Helper_Data);
    }

    public function testConstants()
    {
        $hepler = new Sendit_Bliskapaczka_Helper_Data();

        $this->assertEquals('carriers/sendit_bliskapaczka/parcel_size_type', $hepler::PARCEL_SIZE_TYPE_XML_PATH);
        $this->assertEquals(
            'carriers/sendit_bliskapaczka/parcel_size_type_fixed_size_x',
            $hepler::PARCEL_TYPE_FIXED_SIZE_X_XML_PATH
        );
        $this->assertEquals(
            'carriers/sendit_bliskapaczka/parcel_size_type_fixed_size_y',
            $hepler::PARCEL_TYPE_FIXED_SIZE_Y_XML_PATH
        );
        $this->assertEquals(
            'carriers/sendit_bliskapaczka/parcel_size_type_fixed_size_z',
            $hepler::PARCEL_TYPE_FIXED_SIZE_Z_XML_PATH
        );
        $this->assertEquals(
            'carriers/sendit_bliskapaczka/parcel_size_type_fixed_size_weight',
            $hepler::PARCEL_TYPE_FIXED_SIZE_WEIGHT_XML_PATH
        );

        $this->assertEquals(
            'carriers/sendit_bliskapaczka/bliskapaczkaapikey',
            $hepler::API_KEY_XML_PATH
        );
        $this->assertEquals(
            'carriers/sendit_bliskapaczka/test_mode',
            $hepler::API_TEST_MODE_XML_PATH
        );

        $this->assertEquals(
            'carriers/sendit_bliskapaczka/sender_email',
            $hepler::SENDER_EMAIL
        );
        $this->assertEquals(
            'carriers/sendit_bliskapaczka/sender_first_name',
            $hepler::SENDER_FIRST_NAME
        );
        $this->assertEquals(
            'carriers/sendit_bliskapaczka/sender_last_name',
            $hepler::SENDER_LAST_NAME
        );
        $this->assertEquals(
            'carriers/sendit_bliskapaczka/sender_phone_number',
            $hepler::SENDER_PHONE_NUMBER
        );
        $this->assertEquals(
            'carriers/sendit_bliskapaczka/sender_street',
            $hepler::SENDER_STREET
        );
        $this->assertEquals(
            'carriers/sendit_bliskapaczka/sender_building_number',
            $hepler::SENDER_BUILDING_NUMBER
        );
        $this->assertEquals(
            'carriers/sendit_bliskapaczka/sender_flat_number',
            $hepler::SENDER_FLAT_NUMBER
        );
        $this->assertEquals(
            'carriers/sendit_bliskapaczka/sender_post_code',
            $hepler::SENDER_POST_CODE
        );
        $this->assertEquals(
            'carriers/sendit_bliskapaczka/sender_city',
            $hepler::SENDER_CITY
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

        $hepler = new Sendit_Bliskapaczka_Helper_Data();

        $lowestPrice = $hepler->getLowestPrice(json_decode($priceListEachOther));
        $this->assertEquals(5.99, $lowestPrice);

        $lowestPrice = $hepler->getLowestPrice(json_decode($priceListOneTheSame));
        $this->assertEquals(8.99, $lowestPrice);

        $lowestPrice = $hepler->getLowestPrice(json_decode($priceListOnlyOne));
        $this->assertEquals(10.27, $lowestPrice);
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
        $hepler = new Sendit_Bliskapaczka_Helper_Data();

        $price = $hepler->getPriceForCarrier(json_decode($priceList), 'INPOST');
        $this->assertEquals(10.27, $price);

        $price = $hepler->getPriceForCarrier(json_decode($priceList), 'RUCH');
        $this->assertEquals(5.99, $price);

        $price = $hepler->getPriceForCarrier(json_decode($priceList), 'POCZTA');
        $this->assertEquals(8.99, $price);
    }

    public function testGetPrices()
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
        $hepler = new Sendit_Bliskapaczka_Helper_Data();

        $prices = $hepler->getPrices(json_decode($priceList));

        $this->assertEquals(10.27, $prices['INPOST']);
        $this->assertEquals(5.99, $prices['RUCH']);
        $this->assertTrue(!isset($prices['POCZTA']));
    }

    public function testGetDisabledOperators()
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
        $hepler = new Sendit_Bliskapaczka_Helper_Data();

        $disabledArray = $hepler->getDisabledOperators(json_decode($priceList));

        $this->assertTrue(in_array("POCZTA", $disabledArray));
    }

    /**
     * @dataProvider phpneNumbers
     */
    public function testCleaningPhoneNumber($phoneNumber)
    {
        $hepler = new Sendit_Bliskapaczka_Helper_Data();
     
        $this->assertEquals('606606606', $hepler->telephoneNumberCeaning($phoneNumber));
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
        $hepler = new Sendit_Bliskapaczka_Helper_Data();

        $mode = $hepler->getApiMode(1);
        $this->assertEquals('test', $mode);

        $mode = $hepler->getApiMode(0);
        $this->assertEquals('prod', $mode);

        $mode = $hepler->getApiMode();
        $this->assertEquals('prod', $mode);
    }
}
