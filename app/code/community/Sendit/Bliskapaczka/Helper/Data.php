<?php

use Bliskapaczka\ApiClient;

/**
 * Bliskapaczka helper
 *
 * @author Mateusz Koszutowski (mkoszutowski@divante.pl)
 */
class Sendit_Bliskapaczka_Helper_Data extends Mage_Core_Helper_Data
{
    const DEFAULT_GOOGLE_API_KEY = 'AIzaSyCUyydNCGhxGi5GIt5z5I-X6hofzptsRjE';

    const PARCEL_SIZE_TYPE_XML_PATH = 'carriers/sendit_bliskapaczka/parcel_size_type';
    const PARCEL_TYPE_FIXED_SIZE_X_XML_PATH = 'carriers/sendit_bliskapaczka/parcel_size_type_fixed_size_x';
    const PARCEL_TYPE_FIXED_SIZE_Y_XML_PATH = 'carriers/sendit_bliskapaczka/parcel_size_type_fixed_size_y';
    const PARCEL_TYPE_FIXED_SIZE_Z_XML_PATH = 'carriers/sendit_bliskapaczka/parcel_size_type_fixed_size_z';
    const PARCEL_TYPE_FIXED_SIZE_WEIGHT_XML_PATH = 'carriers/sendit_bliskapaczka/parcel_size_type_fixed_size_weight';
    const PARCEL_DEFAULT_SIZE_X = 12;
    const PARCEL_DEFAULT_SIZE_Y = 12;
    const PARCEL_DEFAULT_SIZE_Z = 16;
    const PARCEL_DEFAULT_SIZE_WEIGHT = 1;
    const COD_BANK_ACCOUNT_NUMBER = 'carriers/sendit_bliskapaczka/cod_bank_account_number';

    const SENDER_EMAIL = 'carriers/sendit_bliskapaczka/sender_email';
    const SENDER_FIRST_NAME = 'carriers/sendit_bliskapaczka/sender_first_name';
    const SENDER_LAST_NAME = 'carriers/sendit_bliskapaczka/sender_last_name';
    const SENDER_PHONE_NUMBER = 'carriers/sendit_bliskapaczka/sender_phone_number';
    const SENDER_STREET = 'carriers/sendit_bliskapaczka/sender_street';
    const SENDER_BUILDING_NUMBER = 'carriers/sendit_bliskapaczka/sender_building_number';
    const SENDER_FLAT_NUMBER = 'carriers/sendit_bliskapaczka/sender_flat_number';
    const SENDER_POST_CODE = 'carriers/sendit_bliskapaczka/sender_post_code';
    const SENDER_CITY = 'carriers/sendit_bliskapaczka/sender_city';

    const API_KEY_XML_PATH = 'carriers/sendit_bliskapaczka/bliskapaczkaapikey';
    const API_TEST_MODE_XML_PATH = 'carriers/sendit_bliskapaczka/test_mode';
    const API_AUTO_ADVICE_XML_PATH = 'carriers/sendit_bliskapaczka/auto_advice';

    const GOOGLE_MAP_API_KEY_XML_PATH = 'carriers/sendit_bliskapaczka/google_map_api_key';

    const SLOW_STATUSES = array('READY_TO_SEND', 'POSTED', 'ON_THE_WAY', 'READY_TO_PICKUP', 'OUT_FOR_DELIVERY',
            'REMINDER_SENT', 'PICKUP_EXPIRED', 'AVIZO', 'RETURNED', 'OTHER', 'MARKED_FOR_CANCELLATION');
    const FAST_STATUSES = array('SAVED', 'WAITING_FOR_PAYMENT', 'PAYMENT_CONFIRMED', 'PAYMENT_REJECTED',
            'PAYMENT_CANCELLATION_ERROR', 'PROCESSING', 'ADVISING', 'ERROR');

    const LOG_FILE = 'sendit.log';

    /**
     * Wrapper on Mage::getStoreConfig
     * We don't able to mock static method
     *
     * @param string $config
     * @return mixed
     */
    public function getStoreConfigWrapper($config)
    {
        return Mage::getStoreConfig($config);
    }

    /**
     * Get parcel dimensions in format accptable by Bliskapaczka API
     *
     * @param string $type
     * @return array
     */
    public function getParcelDimensions($type = 'fixed')
    {
        switch ($type) {
            case 'default':
                $height = self::PARCEL_DEFAULT_SIZE_X;
                $length = self::PARCEL_DEFAULT_SIZE_Y;
                $width = self::PARCEL_DEFAULT_SIZE_Z;
                $weight = self::PARCEL_DEFAULT_SIZE_WEIGHT;
                break;

            default:
                $type = $this->getStoreConfigWrapper(self::PARCEL_SIZE_TYPE_XML_PATH);
                $height = $this->getStoreConfigWrapper(self::PARCEL_TYPE_FIXED_SIZE_X_XML_PATH);
                $length = $this->getStoreConfigWrapper(self::PARCEL_TYPE_FIXED_SIZE_Y_XML_PATH);
                $width = $this->getStoreConfigWrapper(self::PARCEL_TYPE_FIXED_SIZE_Z_XML_PATH);
                $weight = $this->getStoreConfigWrapper(self::PARCEL_TYPE_FIXED_SIZE_WEIGHT_XML_PATH);
                break;
        }

        $dimensions = array(
            "height" => $height,
            "length" => $length,
            "width" => $width,
            "weight" => $weight
        );

        return $dimensions;
    }

    /**
     * Get Google API key. If key is not defined return default.
     *
     * @return string
     */
    public function getGoogleMapApiKey()
    {
        $googleApiKey = self::DEFAULT_GOOGLE_API_KEY;

        if (Mage::getStoreConfig(self::GOOGLE_MAP_API_KEY_XML_PATH)) {
            $googleApiKey = Mage::getStoreConfig(self::GOOGLE_MAP_API_KEY_XML_PATH);
        }

        return $googleApiKey;
    }

    /**
     * Get lowest price from pricing list
     *
     * @param array $priceList
     * @param array $allRates
     * @param boot $cod
     * @return float
     */
    public function getLowestPrice($priceList, $allRates, $cod = false)
    {
        $lowestPrice = null;

        $rates = array();
        foreach ($allRates as $rate) {
            $rates[$rate->getCode()] = $rate;
        }

        foreach ($priceList as $carrier) {
            if ($carrier->availabilityStatus == false
                || !isset($rates['sendit_bliskapaczka_' . $carrier->operatorName . ($cod ? '_COD' : '')])
            ) {
                continue;
            }

            $price = $this->_getPriceWithCartRules($carrier, $rates, $cod);

            if ($lowestPrice == null || $lowestPrice > $price) {
                $lowestPrice = $price;
            }
        }

        return $lowestPrice;
    }

    /**
     * Get price for specific carrier
     *
     * @param array $priceList
     * @param array $allRates
     * @param string $carrierName
     * @param boot $cod
     * @return float|false
     */
    public function getPriceForCarrier($priceList, $allRates, $carrierName, $cod = false)
    {
        $rates = array();
        foreach ($allRates as $rate) {
            $code = $rate->getCode();
            if (is_null($code)) {
                $code = $rate->getCarrier() .'_'. $rate->getMethod(). ($cod ? '_COD' : '');
            }
            $rates[$code] = $rate;
        }

        foreach ($priceList as $carrier) {
            if ($carrier->operatorName == $carrierName
                && $rates['sendit_bliskapaczka_' . $carrierName . ($cod ? '_COD' : '')]
            ) {
                return $this->_getPriceWithCartRules($carrier, $rates, $cod);
            }
        }

        return false;
    }

    /**
     * Get price with applied cart rules
     *
     * @param sdtClass $carrier
     * @param array $rates
     * @param boot $cod
     * @return float
     */
    protected function _getPriceWithCartRules($carrier, $rates, $cod)
    {
        $price = $carrier->price->gross;
        $priceFromMagento = $rates['sendit_bliskapaczka_' . $carrier->operatorName . ($cod ? '_COD' : '')]->getPrice();
        $price = $priceFromMagento < $price ? $priceFromMagento : $price;

        return $price;
    }

    /**
     * Get operators and prices from Bliskapaczka API
     *
     * @param boot $cod
     * @param string $parcelDimensionsType
     * @return array
     */
    public function getPriceList($cod = null, $parcelDimensionsType = 'fixed')
    {
        $apiHelper = new Sendit_Bliskapaczka_Helper_Api();
        $apiClient = $apiHelper->getApiClientPricing($this);

        $data = array("parcel" => array('dimensions' => $this->getParcelDimensions($parcelDimensionsType)));
        if ($cod) {
            $data['codValue'] = 1;
        }

        try {
            $priceList = json_decode($apiClient->get($data));
            $priceListCleared = array();
            foreach ($priceList as $carrier) {
                if ($carrier->availabilityStatus == false) {
                    continue;
                }

                $priceListCleared[] = $carrier;
            }
        } catch (Exception $e) {
            $priceListCleared = array();
            Mage::log($e->getMessage(), null, Sendit_Bliskapaczka_Helper_Data::LOG_FILE);
        }

        return $priceListCleared;
    }

    /**
     * Get widget configuration
     *
     * @param array $allRates
     * @param array $priceList
     * @param boot $cod
     *
     * @return string
     */
    public function getOperatorsForWidget($allRates, $priceList = null, $cod = null)
    {
        if ($priceList == null) {
            $priceList = $this->getPriceList($cod);
        }

        $operators = array();
        $rates = array();
        foreach ($allRates as $rate) {
            $rates[$rate->getCode()] = $rate;
        }

        foreach ($priceList as $carrier) {
            if ($carrier->availabilityStatus == false
                || !$rates['sendit_bliskapaczka_' . $carrier->operatorName . ($cod ? '_COD' : '')]
            ) {
                continue;
            }

            $price = $this->_getPriceWithCartRules($carrier, $rates, $cod);

            $operators[] = array(
                "operator" => $carrier->operatorName,
                "price"    => $price
            );
        }

        return json_encode($operators);
    }

    /**
     * Get Bliskapaczka API Client
     *
     * @return ApiClient\Bliskapaczka\Pricing
     * @throws ApiClient\Exception
     */
    public function getApiClientPricing()
    {
        return (new Sendit_Bliskapaczka_Helper_Api())->getApiClientPricing($this);
    }

    /**
     * Remove all non numeric chars from phone number
     *
     * @param string $phoneNumber
     * @return string
     */
    public function telephoneNumberCleaning($phoneNumber)
    {
        $phoneNumber = preg_replace("/[^0-9]/", "", $phoneNumber);

        if (strlen($phoneNumber) > 9) {
            $phoneNumber = preg_replace("/^48/", "", $phoneNumber);
        }

        return $phoneNumber;
    }

    /**
     * Get API mode
     *
     * @param string $configValue
     * @return string
     */
    public function getApiMode($configValue = null)
    {
        switch ($configValue) {
            case '1':
                $mode = 'test';
                break;

            default:
                $mode = 'prod';
                break;
        }

        return $mode;
    }

    /**
     * Check if shipping method is courier
     *
     * @param string $method
     * @return string
     */
    public function isCourier($method)
    {
        $shortMethodName = $this->_getShortMethodName($method);

        if ($shortMethodName == 'courier') {
            return true;
        }

        return false;
    }

    /**
     * Check if shipping method is to point
     *
     * @param string $method
     * @return string
     */
    public function isPoint($method)
    {
        $shortMethodName = $this->_getShortMethodName($method);

        if ($shortMethodName == 'point') {
            return true;
        }

        return false;
    }

    /**
     * @param int $bliskaOrderId
     */
    public function cancel($bliskaOrderId)
    {
        $bliskaOrder = Mage::getModel('sendit_bliskapaczka/order')->load($bliskaOrderId);
        $bliskaOrder->cancel()->save();
    }

    /**
     * @param int $bliskaOrderId
     */
    public function advice($bliskaOrderId)
    {
        $bliskaOrder = Mage::getModel('sendit_bliskapaczka/order')->load($bliskaOrderId);
        $bliskaOrder->advice();
    }

    /**
     * Short name for shipping method
     *
     * @param string $method
     * @return string
     */
    protected function _getShortMethodName($method)
    {
        if ($method == 'bliskapaczka_sendit_bliskapaczka' || $method == 'bliskapaczka_sendit_bliskapaczka_COD') {
            return 'point';
        }
        return 'courier';

    }
}
