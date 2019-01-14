<?php

/**
 * Class to map order data to data acceptable by Sendit Bliskapaczka API
 */
class Sendit_Bliskapaczka_Model_Mapper_Order extends Sendit_Bliskapaczka_Model_Mapper_Abstract
{

    /**
     * Prepare mapped data for Bliskapaczka API
     *
     * @param Mage_Sales_Model_Order $order
     * @param Sendit_Bliskapaczka_Helper_Data $helper
     * @param bool $reference
     * @return array
     */
    public function getData(Mage_Sales_Model_Order $order, Sendit_Bliskapaczka_Helper_Data $helper, $reference = false)
    {
        $data = [];

        $shippingAddress = $order->getShippingAddress();

        $data['receiverFirstName'] = $shippingAddress->getFirstname();
        $data['receiverLastName'] = $shippingAddress->getLastname();
        $data['receiverPhoneNumber'] = $helper->telephoneNumberCleaning($shippingAddress->getTelephone());
        $data['receiverEmail'] = $shippingAddress->getEmail();

        $data['deliveryType'] = 'P2P';

        if (strpos($shippingAddress->getPosOperator(), '_COD') !== false) {
            $data['codValue'] = (string)round(floatval($order->getGrandTotal()), 2);
        }

        $operatorName = str_replace('_COD', '', $shippingAddress->getPosOperator());
        $data['operatorName'] = $operatorName;

        $data['destinationCode'] = $shippingAddress->getPosCode();

        $data['additionalInformation'] = $order->getIncrementId();
        if ($reference) {
            $data['reference'] = $order->getIncrementId();
        }

        $data['parcel'] = [
            'dimensions' => $this->_getParcelDimensions($helper)
        ];

        $data = $this->_prepareSenderData($data, $helper);

        return $data;
    }
}
