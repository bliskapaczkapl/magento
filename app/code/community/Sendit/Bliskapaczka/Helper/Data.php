<?php

use Bliskapaczka\ApiClient;
use Neodynamic\SDK\Web\WebClientPrint;
use Neodynamic\SDK\Web\DefaultPrinter;
use Neodynamic\SDK\Web\InstalledPrinter;
use Neodynamic\SDK\Web\PrintFile;
use Neodynamic\SDK\Web\PrintFilePDF;
use Neodynamic\SDK\Web\ClientPrintJob;
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
    const API_AUTO_ADVICE_XML_PATH = 'carriers/sendit_bliskapaczka/auto_advice';

    const GOOGLE_MAP_API_KEY_XML_PATH = 'carriers/sendit_bliskapaczka/google_map_api_key';

    const SLOW_STATUSES = array('READY_TO_SEND', 'POSTED', 'ON_THE_WAY', 'READY_TO_PICKUP', 'OUT_FOR_DELIVERY',
            'REMINDER_SENT', 'PICKUP_EXPIRED', 'AVIZO', 'RETURNED', 'OTHER', 'MARKED_FOR_CANCELLATION');
    const FAST_STATUSES = array('SAVED', 'WAITING_FOR_PAYMENT', 'PAYMENT_CONFIRMED', 'PAYMENT_REJECTED',
            'PAYMENT_CANCELLATION_ERROR', 'PROCESSING', 'ADVISING', 'ERROR');

    const LOG_FILE = 'sendit.log';

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
     * @param boot $cod
     *
     * @return string
     */
    public function getPriceList($cod = null)
    {
        $apiClient = $this->getApiClientPricing();

        $data = array("parcel" => array('dimensions' => $this->getParcelDimensions()));
        if ($cod) {
            $data['codValue'] = 1;
        }

        try {
            $priceList = $apiClient->get($data);
        } catch (Exception $e) {
            $priceList = '{}';
            Mage::log($e->getMessage(), null, Sendit_Bliskapaczka_Helper_Data::LOG_FILE);
        }

        $priceList = json_decode($priceList);

        return $priceList;
    }

    /**
     * Get widget configuration
     *
     * @param array $priceList
     * @param float $priceFromCarrier
     * @param boot $cod
     *
     * @return array
     */
    public function getOperatorsForWidget($priceList = null, $priceFromCarrier = null, $cod = null)
    {
        $priceListFromApi = $this->getPriceList($cod);
        $operators = array();

        if (!empty($priceListFromApi)) {
            foreach ($priceListFromApi as $operator) {
                $priceFromMagento = $this->getPriceForCarrier($priceList, $operator->operatorName);
                $price = $operator->price->gross;
                $price = $price < $priceFromMagento ? $price : $priceFromMagento;
                if ($operator->availabilityStatus != false) {
                    if ($priceFromCarrier <= 0.0001) {
                        $price = 0;
                    }

                    $operators[] = array(
                        "operator" => $operator->operatorName,
                        "price"    => $price
                    );
                }
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
    public function getApiClientConfirm()
    {
        $apiClient = new \Bliskapaczka\ApiClient\Bliskapaczka\Order\Confirm(
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
     * Get Bliskapaczka API Client
     *
     * @return \Bliskapaczka\ApiClient\Bliskapaczka
     */
    public function getApiClientPos()
    {
        $apiClient = new \Bliskapaczka\ApiClient\Bliskapaczka\Pos(
            Mage::getStoreConfig(self::API_KEY_XML_PATH),
            $this->getApiMode(Mage::getStoreConfig(self::API_TEST_MODE_XML_PATH))
        );

        return $apiClient;
    }

    /**
     * Get Bliskapaczka API Client
     *
     * @param string $method
     * @return mixed
     */
    public function getApiClientForOrder($method) {
        $autoAdvice = Mage::getStoreConfig(self::API_AUTO_ADVICE_XML_PATH);

        $methodName = $this->getApiClientForOrderMethodName($method, $autoAdvice);

        return $this->{$methodName}();
    }

    /**
     * Get Bliskapaczka API Client
     *
     * @param string $method
     * @return mixed
     */
    public function getApiClientForAdvice($method)
    {
        $methodName = $this->getApiClientForAdviceMethodName($method, '1');

        return $this->{$methodName}();
    }

    /**
     * Get method name to bliskapaczka api client create order action
     *
     * @param string $method
     * @param string $autoAdvice
     * @return string
     */
    public function getApiClientForOrderMethodName($method, $autoAdvice)
    {
        switch ($method) {
            case 'bliskapaczka_sendit_bliskapaczka':
            case 'bliskapaczka_sendit_bliskapaczka_COD':
                $type = 'Order';
                break;

            default:
                $type = 'Todoor';
        }

        $methodName = 'getApiClient' . $type;

        if ($autoAdvice) {
            $methodName .= 'Advice';
        }

        return $methodName;
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
     * For choosen orders create string with order numbers to get data from API
     * @return string
     */
    public function prepareDataForMassActionReport()
    {
        $entityIds = $this->_getRequest()->getParam('entity_id');

        $bliskaOrderCollection = Mage::getModel('sendit_bliskapaczka/order')->getCollection();

        if ($entityIds) {
            $bliskaOrderCollection->addFieldToSelect('*');
            $bliskaOrderCollection->addFieldToFilter('entity_id', array('in' => $entityIds));
        }

        $numbers = '';
        foreach ($bliskaOrderCollection as $bliskaOrder) {
            if ($numbers && $bliskaOrder->getNumber()) {
                $numbers .= ',' . $bliskaOrder->getNumber();
            } else {
                $numbers = $bliskaOrder->getNumber();
            }
        }

        return $numbers;
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
}
