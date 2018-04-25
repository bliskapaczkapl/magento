<?php

/**
 * Class Sendit_Bliskapaczka_Model_Order
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
    protected $_cancelStatuses = array(self::MARKED_FOR_CANCELLATION_STATUS);
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

        /* @var $apiClient \Bliskapaczka\ApiClient\Bliskapaczka */
        $apiClient = $senditHelper->getApiClientCancel();

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

        /* @var $apiClient \Bliskapaczka\ApiClient\Bliskapaczka */
        $apiClient = $senditHelper->getApiClientWaybill();

        $apiClient->setOrderId($this->getNumber());

        $response = $apiClient->get();

        $decodedResponse = json_decode($response);

        $properResponse = $decodedResponse instanceof stdClass && empty($decodedResponse->errors);

        //checking reposponce
        if ($response && $properResponse) {

            return $decodedResponse->url;
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

        /** @var $coreHelper Mage_Core_Helper_Data */
        $coreHelper = Mage::helper('core');

        /* @var $apiClient \Bliskapaczka\ApiClient\Bliskapaczka\Order */
        $apiClient = $senditHelper->getApiClientOrder();

        $apiClient->setOrderId($this->getNumber());

        $response = $apiClient->get();

        $decodedResponse = json_decode($response);

        $properResponse = $decodedResponse instanceof stdClass && empty($decodedResponse->errors);

        //checking reposponce
        if ($response && $properResponse) {
            $bliskaOrder = Mage::getModel('sendit_bliskapaczka/order')->load($this->getId());
            $bliskaOrder->setNumber($coreHelper->stripTags($decodedResponse->number));
            $bliskaOrder->setStatus($coreHelper->stripTags($decodedResponse->status));
            $bliskaOrder->setDeliveryType($coreHelper->stripTags($decodedResponse->deliveryType));
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
        if (in_array($status, $this->_sentStatuses) && !$order->getShipmentsCollection()->setPageSize(1, 1)->getLastItem()->getId()) {
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
        if ($method == 'bliskapaczka_sendit_bliskapaczka') {
            /* @var Sendit_Bliskapaczka_Helper_Data $mapper */
            $mapper = Mage::getModel('sendit_bliskapaczka/mapper_order');
        }

        if ($method == 'bliskapaczka_courier_sendit_bliskapaczka_courier') {
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

        if (
            $method != 'bliskapaczka_sendit_bliskapaczka'
            && $method != 'bliskapaczka_courier_sendit_bliskapaczka_courier'
        ) {
            return $this;
        }

        /* @var $senditHelper Sendit_Bliskapaczka_Helper_Data */
        $senditHelper = Mage::helper('sendit_bliskapaczka');

        $mapper = $this->getMapper($method);

        $data      = $mapper->getData($order, $senditHelper);
        $apiClient = $senditHelper->getApiClientForAdvice($method);

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
}