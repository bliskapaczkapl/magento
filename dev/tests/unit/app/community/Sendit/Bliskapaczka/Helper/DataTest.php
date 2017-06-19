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
}
