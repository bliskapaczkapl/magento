<?php

/**
 * Class to map order data to data acceptable by Sendit Bliskapaczka API
 */
class Sendit_Bliskapaczka_Model_Mapper_Order
{

    /**
     * Prepare mapped data for Bliskapaczka API
     *
     * @param Mage_Sales_Model_Order $order
     * @return array
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

        $data['parcel'] = [
            'dimensions' => $this->_getParcelDimensions()
        ];

        $data = $this->_prepareSenderData($data);

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

    /**
     * Prepare sender data in fomrat accptable by Bliskapaczka API
     *
     * @param array $data
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _prepareSenderData($data)
    {
        if (Mage::getStoreConfig(Sendit_Bliskapaczka_Helper_Data::SENDER_EMAIL)) {
            $data['senderEmail'] = Mage::getStoreConfig(Sendit_Bliskapaczka_Helper_Data::SENDER_EMAIL);
        }

        if (Mage::getStoreConfig(Sendit_Bliskapaczka_Helper_Data::SENDER_FIRST_NAME)) {
            $data['senderFirstName'] = Mage::getStoreConfig(Sendit_Bliskapaczka_Helper_Data::SENDER_FIRST_NAME);
        }

        if (Mage::getStoreConfig(Sendit_Bliskapaczka_Helper_Data::SENDER_LAST_NAME)) {
            $data['senderLastName'] = Mage::getStoreConfig(Sendit_Bliskapaczka_Helper_Data::SENDER_LAST_NAME);
        }

        if (Mage::getStoreConfig(Sendit_Bliskapaczka_Helper_Data::SENDER_PHONE_NUMBER)) {
            $data['senderPhoneNumber'] = Mage::getStoreConfig(Sendit_Bliskapaczka_Helper_Data::SENDER_PHONE_NUMBER);
        }

        if (Mage::getStoreConfig(Sendit_Bliskapaczka_Helper_Data::SENDER_STREET)) {
            $data['senderStreet'] = Mage::getStoreConfig(Sendit_Bliskapaczka_Helper_Data::SENDER_STREET);
        }

        if (Mage::getStoreConfig(Sendit_Bliskapaczka_Helper_Data::SENDER_BUILDING_NUMBER)) {
            $data['senderBuildingNumber'] = Mage::getStoreConfig(Sendit_Bliskapaczka_Helper_Data::SENDER_BUILDING_NUMBER);
        }

        if (Mage::getStoreConfig(Sendit_Bliskapaczka_Helper_Data::SENDER_FLAT_NUMBER)) {
            $data['senderFlatNumber'] = Mage::getStoreConfig(Sendit_Bliskapaczka_Helper_Data::SENDER_FLAT_NUMBER);
        }

        if (Mage::getStoreConfig(Sendit_Bliskapaczka_Helper_Data::SENDER_POST_CODE)) {
            $data['senderPostCode'] = Mage::getStoreConfig(Sendit_Bliskapaczka_Helper_Data::SENDER_POST_CODE);
        }

        if (Mage::getStoreConfig(Sendit_Bliskapaczka_Helper_Data::SENDER_CITY)) {
            $data['senderCity'] = Mage::getStoreConfig(Sendit_Bliskapaczka_Helper_Data::SENDER_CITY);
        }

        return $data;
    }
}
