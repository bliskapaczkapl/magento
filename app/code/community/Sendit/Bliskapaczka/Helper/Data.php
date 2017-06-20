<?php

use Bliskapaczka\ApiClient;

/**
 * Bliskapaczka helper
 *
 * @author Mateusz Koszutowski (mkoszutowski@divante.pl)
 */
class Sendit_Bliskapaczka_Helper_Data extends Mage_Core_Helper_Data
{
    const PARCEL_SIZE_TYPE_XML_PATH = 'carriers/sendit_bliskapaczka/parcel_size_type';
    const PARCEL_TYPE_FIXED_SIZE_X_XML_PATH = 'carriers/sendit_bliskapaczka/parcel_size_type_fixed_size_x';
    const PARCEL_TYPE_FIXED_SIZE_Y_XML_PATH = 'carriers/sendit_bliskapaczka/parcel_size_type_fixed_size_y';
    const PARCEL_TYPE_FIXED_SIZE_Z_XML_PATH = 'carriers/sendit_bliskapaczka/parcel_size_type_fixed_size_z';
    const PARCEL_TYPE_FIXED_SIZE_WEIGHT_XML_PATH = 'carriers/sendit_bliskapaczka/parcel_size_type_fixed_size_weight';
    
    const API_KEY_XML_PATH = 'carriers/sendit_bliskapaczka/bliskapaczkaapikey';

    /**
     * Get parcel dimensions in format accptable by Bliskapaczka API
     *
     * @return array
     */
    public function getParcelDimensions()
    {
        $type = Mage::getStoreConfig(self::PARCEL_SIZE_TYPE_XML_PATH);
        $height = Mage::getStoreConfig(self::PARCEL_TYPE_FIXED_SIZE_X_XML_PATH);
        $length = Mage::getStoreConfig(self::PARCEL_TYPE_FIXED_SIZE_Y_XML_PATH);
        $width = Mage::getStoreConfig(self::PARCEL_TYPE_FIXED_SIZE_Z_XML_PATH);
        $weight = Mage::getStoreConfig(self::PARCEL_TYPE_FIXED_SIZE_WEIGHT_XML_PATH);

        $dimensions = array(
            "height" => $height,
            "length" => $length,
            "width" => $width,
            "weight" => $weight
        );

        return $dimensions;
    }

    /**
     * Get lowest price from pricing list
     *
     * @param array $priceList
     * @return float
     */
    public function getLowestPrice($priceList)
    {
        $lowestPrice = null;

        foreach ($priceList as $carrier) {
            if ($carrier->availabilityStatus == false) {
                continue;
            }

            if ($lowestPrice == null || $lowestPrice > $carrier->price->gross) {
                $lowestPrice = $carrier->price->gross;
            }
        }

        return $lowestPrice;
    }

    /**
     * Get price for specific carrier
     *
     * @param array $priceList
     * @param string $carrierName
     * @return float
     */
    public function getPriceForCarrier($priceList, $carrierName)
    {
        foreach ($priceList as $carrier) {
            if ($carrier->operatorName == $carrierName) {
                return $carrier->price->gross;
            }

        }
    }

    /**
     * Get prices from pricing list
     *
     * @param array $priceList
     * @return array
     */
    public function getPrices($priceList)
    {
        $prices = array();

        foreach ($priceList as $carrier) {
            if ($carrier->price == null) {
                continue;
            }

            $prices[$carrier->operatorName] = $carrier->price->gross;
        }

        return $prices;
    }

    /**
     * Get disabled operators from pricing list
     *
     * @param array $priceList
     * @return array
     */
    public function getDisabledOperators($priceList)
    {
        $disabled = array();

        foreach ($priceList as $carrier) {
            if ($carrier->availabilityStatus == false) {
                $disabled[] = $carrier->operatorName;
            }
        }

        return $disabled;
    }

    /**
     * Get prices in format accptable by Bliskapaczka Widget
     *
     * @return string
     */
    public function getPricesForWidget()
    {
        $apiClient = $this->getApiClient();
        $priceList = $apiClient->getPricing(
            array("parcels" => array(array('dimensions' => $this->getParcelDimensions())))
        );

        $pricesJson = json_encode($this->getPrices(json_decode($priceList)));

        return $pricesJson;
    }

    /**
     * Get disabled operators in format accptable by Bliskapaczka Widget
     *
     * @return array
     */
    public function getDisabledOperatorsForWidget()
    {
        $apiClient = $this->getApiClient();
        $priceList = $apiClient->getPricing(
            array("parcels" => array(array('dimensions' => $this->getParcelDimensions())))
        );

        $disabledArray = $this->getDisabledOperators(json_decode($priceList));

        return $disabledArray;
    }

    /**
     * Get Bliskapaczka API Client
     *
     * @return \Bliskapaczka\ApiClient\Bliskapaczka
     */
    public function getApiClient()
    {
        $apiClient = new \Bliskapaczka\ApiClient\Bliskapaczka(
            Mage::getStoreConfig(self::API_KEY_XML_PATH),
            $this->getApiMode(Mage::getStoreConfig(self::API_TEST_MODE_XML_PATH))
        );

        return $apiClient;
    }

    /**
     * Get API mode
     *
     * @return string
     */
    public function getApiMode($config_value)
    {
        $mode = '';

        switch ($config_value) {
            case '1':
                $mode = 'test';
                break;

            default:
                $mode = 'prod';
                break;
        }

        return $mode;
    }
}
