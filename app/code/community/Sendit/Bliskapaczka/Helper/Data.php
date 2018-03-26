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
    
    const SENDER_EMAIL = 'carriers/sendit_bliskapaczka/sender_email';
    const SENDER_FIRST_NAME = 'carriers/sendit_bliskapaczka/sender_first_name';
    const SENDER_LAST_NAME = 'carriers/sendit_bliskapaczka/sender_last_name';
    const SENDER_PHONE_NUMBER = 'carriers/sendit_bliskapaczka/sender_phone_number';
    const SENDER_STREET = 'carriers/sendit_bliskapaczka/sender_street';
    const SENDER_BUILDING_NUMBER = 'carriers/sendit_bliskapaczka/sender_building_number';
    const SENDER_FLAT_NUMBER = 'carriers/sendit_bliskapaczka/sender_flat_number';
    const SENDER_POST_CODE = 'carriers/sendit_bliskapaczka/sender_post_code';
    const SENDER_CITY = 'carriers/sendit_bliskapaczka/sender_city';

    const TODOOR_SENDER_EMAIL = 'carriers/sendit_bliskapaczka_courier/sender_email';
    const TODOOR_SENDER_FIRST_NAME = 'carriers/sendit_bliskapaczka_courier/sender_first_name';
    const TODOOR_SENDER_LAST_NAME = 'carriers/sendit_bliskapaczka_courier/sender_last_name';
    const TODOOR_SENDER_PHONE_NUMBER = 'carriers/sendit_bliskapaczka_courier/sender_phone_number';
    const TODOOR_SENDER_STREET = 'carriers/sendit_bliskapaczka_courier/sender_street';
    const TODOOR_SENDER_BUILDING_NUMBER = 'carriers/sendit_bliskapaczka_courier/sender_building_number';
    const TODOOR_SENDER_FLAT_NUMBER = 'carriers/sendit_bliskapaczka_courier/sender_flat_number';
    const TODOOR_SENDER_POST_CODE = 'carriers/sendit_bliskapaczka_courier/sender_post_code';
    const TODOOR_SENDER_CITY = 'carriers/sendit_bliskapaczka_courier/sender_city';

    const API_KEY_XML_PATH = 'carriers/sendit_bliskapaczka/bliskapaczkaapikey';
    const API_TEST_MODE_XML_PATH = 'carriers/sendit_bliskapaczka/test_mode';

    const GOOGLE_MAP_API_KEY_XML_PATH = 'carriers/sendit_bliskapaczka/google_map_api_key';

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
     * Get Google API key. If key is not defined return default.
     *
     * @return string
     */
    public function getGoogleMapApiKey()
    {
        $googleApiKey = self::DEFAULT_GOOGLE_API_KEY;

        if (Mage::getStoreConfig(self::GOOGLE_MAP_API_KEY_XML_PATH)){
            $googleApiKey = Mage::getStoreConfig(self::GOOGLE_MAP_API_KEY_XML_PATH);
        }

        return $googleApiKey;
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
     * Get operators and prices from Bliskapaczka API
     *
     * @return string
     */
    public function getPriceList()
    {
        $apiClient = $this->getApiClientPricing();
        $priceList = $apiClient->get(
            array("parcel" => array('dimensions' => $this->getParcelDimensions()))
        );

        return json_decode($priceList);
    }

    /**
     * Get widget configuration
     *
     * @param array $priceList
     * @param float $priceFromCarrier
     * @return array
     */
    public function getOperatorsForWidget($priceList = null, $priceFromCarrier = null)
    {
        if (!$priceList) {
            $priceList = $this->getPriceList();
        }
        $operators = array();

        foreach ($priceList as $operator) {
            $price = $operator->price->gross;
            if ($operator->availabilityStatus != false) {
                if ($priceFromCarrier <= 0.0001) {
                    $price = 0;
                }

                $operators[] = array(
                    "operator" => $operator->operatorName,
                    "price" => $price
                );
            }
        }

        return json_encode($operators);
    }

    /**
     * Get Bliskapaczka API Client
     *
     * @return \Bliskapaczka\ApiClient\Bliskapaczka
     */
    public function getApiClientOrder()
    {
        $apiClient = new \Bliskapaczka\ApiClient\Bliskapaczka\Order(
            Mage::getStoreConfig(self::API_KEY_XML_PATH),
            $this->getApiMode(Mage::getStoreConfig(self::API_TEST_MODE_XML_PATH))
        );

        return $apiClient;
    }

    /**
     * Get Bliskapaczka API Client
     *
     * @return \Bliskapaczka\ApiClient\Bliskapaczka
     */
    public function getApiClientOrderAdvice()
    {
        $apiClient = new \Bliskapaczka\ApiClient\Bliskapaczka\Order\Advice(
            Mage::getStoreConfig(self::API_KEY_XML_PATH),
            $this->getApiMode(Mage::getStoreConfig(self::API_TEST_MODE_XML_PATH))
        );

        return $apiClient;
    }

    /**
     * Get Bliskapaczka API Client
     *
     * @return \Bliskapaczka\ApiClient\Bliskapaczka
     */
    public function getApiClientCancel()
    {
        $apiClient = new \Bliskapaczka\ApiClient\Bliskapaczka\Order\Cancel(
            Mage::getStoreConfig(self::API_KEY_XML_PATH),
            $this->getApiMode(Mage::getStoreConfig(self::API_TEST_MODE_XML_PATH))
        );

        return $apiClient;
    }

    /**
     * Get Bliskapaczka API Client
     *
     * @return \Bliskapaczka\ApiClient\Bliskapaczka
     */
    public function getApiClientGet()
    {
        $apiClient = new \Bliskapaczka\ApiClient\Bliskapaczka\Order\Get(
            Mage::getStoreConfig(self::API_KEY_XML_PATH),
            $this->getApiMode(Mage::getStoreConfig(self::API_TEST_MODE_XML_PATH))
        );

        return $apiClient;
    }

    /**
     * Get Bliskapaczka API Client
     *
     * @return \Bliskapaczka\ApiClient\Bliskapaczka
     */
    public function getApiClientWaybill()
    {
        $apiClient = new \Bliskapaczka\ApiClient\Bliskapaczka\Order\Waybill(
            Mage::getStoreConfig(self::API_KEY_XML_PATH),
            $this->getApiMode(Mage::getStoreConfig(self::API_TEST_MODE_XML_PATH))
        );

        return $apiClient;
    }

    /**
     * Get Bliskapaczka API Client
     *
     * @return \Bliskapaczka\ApiClient\Bliskapaczka
     */
    public function getApiClientPricing()
    {
        $apiClient = new \Bliskapaczka\ApiClient\Bliskapaczka\Pricing(
            Mage::getStoreConfig(self::API_KEY_XML_PATH),
            $this->getApiMode(Mage::getStoreConfig(self::API_TEST_MODE_XML_PATH))
        );

        return $apiClient;
    }

    /**
     * Get Bliskapaczka API Client
     *
     * @return \Bliskapaczka\ApiClient\Bliskapaczka
     */
    public function getApiClientPricingTodoor()
    {
        $apiClient = new \Bliskapaczka\ApiClient\Bliskapaczka\Pricing\Todoor(
            Mage::getStoreConfig(self::API_KEY_XML_PATH),
            $this->getApiMode(Mage::getStoreConfig(self::API_TEST_MODE_XML_PATH))
        );

        return $apiClient;
    }

    /**
     * Get Bliskapaczka API Client
     *
     * @return \Bliskapaczka\ApiClient\Bliskapaczka
     */
    public function getApiClientTodoor()
    {
        $apiClient = new \Bliskapaczka\ApiClient\Bliskapaczka\Todoor(
            Mage::getStoreConfig(self::API_KEY_XML_PATH),
            $this->getApiMode(Mage::getStoreConfig(self::API_TEST_MODE_XML_PATH))
        );

        return $apiClient;
    }

    /**
     * Get Bliskapaczka API Client
     *
     * @return \Bliskapaczka\ApiClient\Bliskapaczka
     */
    public function getApiClientTodoorAdvice()
    {
        $apiClient = new \Bliskapaczka\ApiClient\Bliskapaczka\Todoor\Advice(
            Mage::getStoreConfig(self::API_KEY_XML_PATH),
            $this->getApiMode(Mage::getStoreConfig(self::API_TEST_MODE_XML_PATH))
        );

        return $apiClient;
    }

    /**
     * Get Bliskapaczka API Client
     *
     * @return \Bliskapaczka\ApiClient\Bliskapaczka
     */
    public function getApiClientReport()
    {
        $apiClient = new \Bliskapaczka\ApiClient\Bliskapaczka\Report(
            Mage::getStoreConfig(self::API_KEY_XML_PATH),
            $this->getApiMode(Mage::getStoreConfig(self::API_TEST_MODE_XML_PATH))
        );

        return $apiClient;
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
        $mode = '';

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
     * Download content from URL
     *
     * @param string $url
     *
     * @return string
     */
    public function downloadContent($url)
    {
        $content = '';

        if (!$url) {
            return $content;
        }

        $http = new Varien_Http_Adapter_Curl();
        $http->write('GET', $url);
        $content = $http->read();
        $http->close();

        return $content;
    }

    /**
     * @param string $path
     * @param string $fileName
     * @param string $content
     *
     * @return bool|void
     */
    public function writeFile($path, $fileName, $content)
    {
        if (!$path || !$fileName || !$content) {
            return false;
        }

        $io = new Varien_Io_File();
        $io->setAllowCreateFolders(true);
        $io->open(array('path' => $path));
        if ($io->fileExists($fileName) && !$io->isWriteable($fileName)) {
            // file does not exist or is not readable
            return;
        }

        $io->streamOpen($fileName);
        $io->streamWrite($content);
        $io->streamClose();

        return true;
    }
}
