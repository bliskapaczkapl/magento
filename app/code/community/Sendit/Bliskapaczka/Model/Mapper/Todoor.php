<?php

/**
 * Class to map order data to data acceptable by endpoint todoor (courier) Sendit Bliskapaczka API
 */
class Sendit_Bliskapaczka_Model_Mapper_Todoor extends Sendit_Bliskapaczka_Model_Mapper_Abstract
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

        $data = $this->getShippingAddressData($shippingAddress, $helper);

        $operatorName = str_replace('_COD', '', $shippingAddress->getPosOperator());
        $data['operatorName'] = $operatorName;

        $data['deliveryType'] = 'D2D';
        if ($operatorName == 'POCZTA_P2D') {
            $data['deliveryType'] = 'P2D';
            $data['operatorName'] = 'POCZTA';
        }

        $data['additionalInformation'] = $order->getIncrementId();
        if ($reference) {
            $data['reference'] = $order->getIncrementId();
        }

        $data['parcel'] = [
            'dimensions' => $this->_getParcelDimensions($helper)
        ];

        $data = $this->_prepareSenderData($data, $helper);
        $data = $this->_prepareCodData($data, $order, $helper);

        return $data;
    }

}
