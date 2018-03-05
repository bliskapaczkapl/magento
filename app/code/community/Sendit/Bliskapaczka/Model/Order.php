<?php

/**
 * Class Sendit_Bliskapaczka_Model_Order
 */
class Sendit_Bliskapaczka_Model_Order extends Mage_Core_Model_Abstract
{
    const MARKED_FOR_CANCELLATION_STATUS = 'MARKED_FOR_CANCELLATION';

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
        if ($this->getStatus() == self::MARKED_FOR_CANCELLATION_STATUS) {
            return false;
        }

        return true;
    }
}