<?php

/**
 * Class to map order data to data acceptable by Sendit Bliskapaczka API
 */
class Sendit_Bliskapaczka_Model_Mapper_Order
{

    /**
     * Prepare mapped data
     *
     * @param Mage_Sales_Model_Order $order
     */
    public function getData(Mage_Sales_Model_Order $order)
    {
        $data = [];

        $shippingAddress = $order->getShippingAddress();

        $data['receiverFirstName'] = $shippingAddress->getFirstname();
        $data['receiverLastName'] = $shippingAddress->getLastname();
        $data['receiverPhoneNumber'] = $shippingAddress->getTelephone();
        $data['receiverEmail'] = $shippingAddress->getEmail();

        $data['operatorName'] = $shippingAddress->getPosOperator();
        $data['destinationCode'] = $shippingAddress->getPosCode();

        $data['parcels'] = [
            [
                'dimensions' => $this->_getParcelDimensions()
            ]
        ];

        return $data;
    }

    /**
     * Get parcel dimensions in format accptable by Bliskapaczka API
     *
     * @return array
     */
    protected function _getParcelDimensions()
    {
        /* @var $helper Sendit_Bliskapaczka_Helper_Data */
        $helper = new Sendit_Bliskapaczka_Helper_Data();
        return $helper->getParcelDimensions();
    }
}
