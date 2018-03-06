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
        self::CANCELED
    );

    /**
     * Cancel possible statuses
     *
     * @var array
     */
    protected $_cancelStatuses = array(self::MARKED_FOR_CANCELLATION_STATUS);

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
    public function cancel() {

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
            return $this;
        } else {
            $message = ($decodedResponse ? current($decodedResponse->errors)->message : '');

            //throwing exception
            throw new Exception(Mage::helper('sendit_bliskapaczka')->__('Bliskapaczka: Error or empty API response' . ' ' . $message));
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
    public function waybill() {

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
            throw new Exception(Mage::helper('sendit_bliskapaczka')->__('Bliskapaczka: Error or empty API response' . ' ' . $message));
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
}