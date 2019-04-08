<?php

/**
 * Class Sendit_Bliskapaczka_Model_Order
 *
 * @SuppressWarnings(PHPMD)
 */
class Sendit_Bliskapaczka_Model_Order extends Mage_Core_Model_Abstract
{
    const NEW_STATUS                     = 'NEW';
    const SAVED                          = 'SAVED';
    const WAITING_FOR_PAYMENT            = 'WAITING_FOR_PAYMENT';
    const PAYMENT_CONFIRMED              = 'PAYMENT_CONFIRMED';
    const PAYMENT_REJECTED               = 'PAYMENT_REJECTED';
    const PAYMENT_CANCELLATION_ERROR     = 'PAYMENT_CANCELLATION_ERROR';
    const PROCESSING                     = 'PROCESSING';
    const ADVISING                       = 'ADVISING';
    const ERROR                          = 'ERROR';
    const READY_TO_SEND                  = 'READY_TO_SEND';
    const POSTED                         = 'POSTED';
    const ON_THE_WAY                     = 'ON_THE_WAY';
    const READY_TO_PICKUP                = 'READY_TO_PICKUP';
    const OUT_FOR_DELIVERY               = 'OUT_FOR_DELIVERY';
    const DELIVERED                      = 'DELIVERED';
    const REMINDER_SENT                  = 'REMINDER_SENT';
    const PICKUP_EXPIRED                 = 'PICKUP_EXPIRED';
    const AVIZO                          = 'AVIZO';
    const CLAIMED                        = 'CLAIMED';
    const RETURNED                       = 'RETURNED';
    const ARCHIVED                       = 'ARCHIVED';
    const OTHER                          = 'OTHER';
    const MARKED_FOR_CANCELLATION_STATUS = 'MARKED_FOR_CANCELLATION';
    const CANCELED                       = 'CANCELED';

    const GENERIC_ADVICE_ERROR           = 'GENERIC_ADVICE_ERROR';
    const AUTHORIZATION_ERROR            = 'AUTHORIZATION_ERROR';
    const LABEL_GENERATION_ERROR         = 'LABEL_GENERATION_ERROR';
    const WAYBILL_PROCESS_ERROR          = 'WAYBILL_PROCESS_ERROR';
    const BACKEND_ERROR                  = 'BACKEND_ERROR';

    const GENERIC_ADVICE_ERROR_FOR_HUMANS = "Error (Operator's advising)";
    const LABEL_GENERATION_ERROR_FOR_HUMANS = "Error (Label generating)";
    const WAYBILL_PROCESS_ERROR_FOR_HUMANS = "Error (Label processing)";
    const AUTHORIZATION_ERROR_FOR_HUMANS = "Error (Access data)";
    const BACKEND_ERROR_FOR_HUMANS = "Error (Unrecognized)";

    /**
     * Waybill NOT possible statuses
     *
     * @var array
     */
    protected $_waybillUnavailableStatuses = array(
        self::NEW_STATUS,
        self::SAVED,
        self::WAITING_FOR_PAYMENT,
        self::PAYMENT_CONFIRMED,
        self::PAYMENT_REJECTED,
        self::PAYMENT_CANCELLATION_ERROR,
        self::PROCESSING,
        self::ADVISING,
        self::ERROR,
        self::CANCELED,
    );

    /**
     * Cancel possible statuses
     *
     * @var array
     */
    protected $_cancelStatuses = array(
        self::SAVED,
        self::WAITING_FOR_PAYMENT,
        self::PAYMENT_CONFIRMED,
        self::PAYMENT_REJECTED,
        self::PROCESSING,
        self::READY_TO_SEND,
        self::ERROR
    );

    /**
     * Cancel possible statuses
     *
     * @var array
     */
    protected $_sentStatuses = array(
        self::POSTED,
        self::ON_THE_WAY,
        self::READY_TO_PICKUP,
        self::OUT_FOR_DELIVERY,
        self::DELIVERED,
        self::CANCELED
    );

    /**
     * Advice possible statuses
     *
     * @var array
     */
    protected $_adviceStatuses = array(self::SAVED);

    /**
     * Retry possible statuses
     *
     * @var array
     */
    protected $_retryStatuses = array(self::ERROR);

    /**
     * Retry possible statuses
     *
     * @var array
     */
    protected $_errorReasonStatuses = array(
        self::GENERIC_ADVICE_ERROR,
        self::AUTHORIZATION_ERROR,
        self::LABEL_GENERATION_ERROR,
        self::WAYBILL_PROCESS_ERROR,
        self::BACKEND_ERROR
    );

    /**
     * Model constructor
     */
    public function _construct()
    {
        $this->_init('sendit_bliskapaczka/order');
    }

    /**
     * Cancel Bliska order
     *
     * @return $this
     * @throws Exception
     */
    public function cancel()
    {
        /* @var $senditHelper Sendit_Bliskapaczka_Helper_Data */
        $senditHelper = new Sendit_Bliskapaczka_Helper_Data();
        /* @var $senditApiHelper Sendit_Bliskapaczka_Helper_Api */
        $senditApiHelper = Mage::helper('sendit_bliskapaczka/api');

        /* @var $apiClient \Bliskapaczka\ApiClient\Bliskapaczka */
        $apiClient = $senditApiHelper->getApiClientCancel($senditHelper);

        $apiClient->setOrderId($this->getNumber());

        $response = $apiClient->cancel();

        $decodedResponse = json_decode($response);

        $properResponse = $decodedResponse instanceof stdClass && empty($decodedResponse->errors);

        //checking reposponce
        if ($response && $properResponse) {
            $this->setStatus($decodedResponse->status);

            $this->createShipment($decodedResponse->status);

            return $this;
        } else {
            $message = ($decodedResponse ? current($decodedResponse->errors)->message : '');

            //throwing exception
            throw new Exception(
                Mage::helper('sendit_bliskapaczka')->__('Bliskapaczka: Error or empty API response' . ' ' . $message)
            );
        }
    }

    /**
     * @return bool
     */
    public function canCancel()
    {
        if (in_array($this->getStatus(), $this->_cancelStatuses)) {
            return true;
        }

        return false;
    }

    /**
     * Get waybill url for Bliska order
     *
     * @return $this
     * @throws Exception
     */
    public function waybill()
    {
        /* @var $senditHelper Sendit_Bliskapaczka_Helper_Data */
        $senditHelper = new Sendit_Bliskapaczka_Helper_Data();
        /* @var $senditApiHelper Sendit_Bliskapaczka_Helper_Api */
        $senditApiHelper = Mage::helper('sendit_bliskapaczka/api');

        /* @var $apiClient \Bliskapaczka\ApiClient\Bliskapaczka */
        $apiClient = $senditApiHelper->getApiClientWaybill($senditHelper);

        $apiClient->setOrderId($this->getNumber());

        $response = $apiClient->get();

        $decodedResponse = json_decode($response);

        //checking reposponce
        if ($response && empty($decodedResponse->errors)) {
            return $decodedResponse[0]->url;
        } else {
            $message = ($decodedResponse ? current($decodedResponse->errors)->message : '');

            //throwing exception
            throw new Exception(
                Mage::helper('sendit_bliskapaczka')->__('Bliskapaczka: Error or empty API response' . ' ' . $message)
            );
        }
    }

    /**
     * @return bool
     */
    public function canWaybill()
    {
        if (empty($this->getNumber()) || in_array($this->getStatus(), $this->_waybillUnavailableStatuses)) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function canAdvice()
    {
        if (!empty($this->getNumber()) && in_array($this->getStatus(), $this->_adviceStatuses)) {
            return true;
        }

        return false;
    }

    /**
     * Get order data
     *
     * @return mixed
     * @throws Exception
     */
    public function get()
    {
        /* @var $senditHelper Sendit_Bliskapaczka_Helper_Data */
        $senditHelper = new Sendit_Bliskapaczka_Helper_Data();
        /* @var $senditApiHelper Sendit_Bliskapaczka_Helper_Api */
        $senditApiHelper = Mage::helper('sendit_bliskapaczka/api');

        /** @var $coreHelper Mage_Core_Helper_Data */
        $coreHelper = Mage::helper('core');

        /* @var $apiClient \Bliskapaczka\ApiClient\Bliskapaczka\Order */
        $apiClient = $senditApiHelper->getApiClientOrder($senditHelper);

        $apiClient->setOrderId($this->getNumber());

        $response = $apiClient->get();

        $decodedResponse = json_decode($response);
        $properResponse = $decodedResponse instanceof stdClass && empty($decodedResponse->errors);

        //checking reposponce
        if ($response && $properResponse) {
            $bliskaOrder = Mage::getModel('sendit_bliskapaczka/order')->load($this->getId());
            $bliskaOrder->setNumber($coreHelper->stripTags($decodedResponse->number));

            $bliskaOrder->setStatus($coreHelper->stripTags($decodedResponse->status));
            $bliskaOrder->setErrorReason($coreHelper->stripTags($decodedResponse->errorReason));

            $bliskaOrder->setDeliveryType($coreHelper->stripTags($decodedResponse->deliveryType));

            $bliskaOrder->setPosCode($decodedResponse->destinationCode);
            $bliskaOrder->setPosOperator($decodedResponse->operatorName);

            list($order, $method) = $this->getMethod();

            if ($senditHelper->isPoint($method)) {
                // Get information about point
                $apiClient = $senditApiHelper->getApiClientPos($senditHelper);
                $apiClient->setPointCode($decodedResponse->destinationCode);
                $apiClient->setOperator($decodedResponse->operatorName);
                $posInfo = json_decode($apiClient->get());

                $destination = $posInfo->operator . '</br>' .
                    (($posInfo->description) ? $posInfo->description . '</br>': '') .
                    $posInfo->street . '</br>' .
                    (($posInfo->postalCode) ? $posInfo->postalCode . ' ': '') . $posInfo->city;

                $bliskaOrder->setPosCodeDescription($destination);
            }

            $bliskaOrder->setCreationDate($coreHelper->stripTags($decodedResponse->creationDate));
            $bliskaOrder->setAdviceDate($coreHelper->stripTags($decodedResponse->adviceDate));
            $bliskaOrder->setTrackingNumber($coreHelper->stripTags($decodedResponse->trackingNumber));
            $bliskaOrder->save();

            $this->createShipment($decodedResponse->status);
        } else {
            $message = ($decodedResponse ? current($decodedResponse->errors)->message : '');

            //throwing exception
            throw new Exception(
                Mage::helper('sendit_bliskapaczka')->__('Bliskapaczka: Error or empty API response' . ' ' . $message)
            );
        }
    }

    /**
     * Create shipment based on status
     *
     * @param string $status
     */
    protected function createShipment($status)
    {
        /** @var Mage_Sales_Model_Order */
        $order = Mage::getModel('sales/order')->load($this->getOrderId());

        //if there is no shipment yet
        if (in_array($status, $this->_sentStatuses)
            && !$order->getShipmentsCollection()->setPageSize(1, 1)->getLastItem()->getId()
        ) {
            $shipmentApi2 = Mage::getModel('sales/order_shipment_api_v2');
            $shipmentApi2->create($order->getIncrementId());
        }
    }

    /**
     * @return array
     */
    protected function getMethod()
    {
        $method = '';

        $order = Mage::getModel('sales/order')->load($this->getOrderId());

        if ($order && $order->getId()) {
            $method = $order->getShippingMethod(true)->getMethod();
        }

        return array(
            $order,
            $method,
        );
    }

    /**
     * @param string $method
     *
     * @return Sendit_Bliskapaczka_Helper_Data
     */
    protected function getMapper($method)
    {
        if ($method == 'bliskapaczka_sendit_bliskapaczka'
            || $method == 'bliskapaczka_sendit_bliskapaczka_COD'
        ) {
            /* @var Sendit_Bliskapaczka_Helper_Data $mapper */
            $mapper = Mage::getModel('sendit_bliskapaczka/mapper_order');
        } else {
            /* @var Sendit_Bliskapaczka_Helper_Data $mapper */
            $mapper = Mage::getModel('sendit_bliskapaczka/mapper_todoor');
        }

        return $mapper;
    }

    /**
     * Advice order
     *
     * @return $this
     * @throws Exception
     */
    public function advice()
    {
        /** @var $coreHelper Mage_Core_Helper_Data */
        $coreHelper = Mage::helper('core');

        list($order, $method) = $this->getMethod();

        if (strpos($method, 'bliskapaczka') === false) {
            return $this;
        }

        /* @var $senditHelper Sendit_Bliskapaczka_Helper_Data */
        $senditHelper = Mage::helper('sendit_bliskapaczka');

        $mapper = $this->getMapper($method);

        $useReference = Mage::getStoreConfig(Sendit_Bliskapaczka_Model_Carrier_Bliskapaczka::REFERENCE_SWITCH);
        $data = $mapper->getData($order, $senditHelper, $useReference);
        /* @var $senditApiHelper Sendit_Bliskapaczka_Helper_Api */
        $senditApiHelper = Mage::helper('sendit_bliskapaczka/api');

        $apiClient = $senditApiHelper->getApiClientForOrder($method, '', $senditHelper, true);
        $apiClient->setOrderId($this->getNumber());

        $response = $apiClient->create($data);

        $decodedResponse = json_decode($response);

        $properResponse = $decodedResponse instanceof stdClass && empty($decodedResponse->errors);

        //checking reposponce
        if ($response && $properResponse) {
            $bliskaOrder = Mage::getModel('sendit_bliskapaczka/order')->load($this->getId());
            $bliskaOrder->setStatus($coreHelper->stripTags($decodedResponse->status));
            $bliskaOrder->setAdviceDate($coreHelper->stripTags($decodedResponse->adviceDate));
            $bliskaOrder->save();
        } else {
            $message = ($decodedResponse ? current($decodedResponse->errors)->message : '');

            //throwing exception
            throw new Exception(
                Mage::helper('sendit_bliskapaczka')->__('Bliskapaczka: Error or empty API response' . ' ' . $message)
            );
        }
    }

    /**
     * @return bool
     */
    public function canRetry()
    {
        if (!empty($this->getNumber())
            && in_array($this->getStatus(), $this->_retryStatuses)
            && in_array($this->getErrorReason(), $this->_errorReasonStatuses)
        ) {
            return true;
        }

        return false;
    }

    /**
     * Advice order
     *
     * @return $this
     * @throws Exception
     */
    public function retry()
    {
        list($order, $method) = $this->getMethod();

        if (strpos($method, 'bliskapaczka') === false) {
            return $this;
        }

        /* @var $senditHelper Sendit_Bliskapaczka_Helper_Data */
        $senditHelper = Mage::helper('sendit_bliskapaczka');
        /* @var $senditApiHelper Sendit_Bliskapaczka_Helper_Api */
        $senditApiHelper = Mage::helper('sendit_bliskapaczka/api');
        $apiClient = $senditApiHelper->getApiClientRetry($senditHelper);
        $apiClient->setOrderId($this->getNumber());

        try {
            $apiClient->retry();
            $this->get();
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, Sendit_Bliskapaczka_Helper_Data::LOG_FILE);
            Mage::throwException($senditHelper->__($e->getMessage()));
        }
    }
}
