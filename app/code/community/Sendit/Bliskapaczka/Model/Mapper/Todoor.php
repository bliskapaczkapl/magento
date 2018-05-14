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
     * @return array
     */
    public function getData(Mage_Sales_Model_Order $order, Sendit_Bliskapaczka_Helper_Data $helper)
    {
        $data = [];

        $shippingAddress = $order->getShippingAddress();

        $fullStreet = $shippingAddress->getStreet()[0];
        $street = preg_split("/\s+(?=\S*+$)/" , $fullStreet);

        $data['receiverFirstName'] = $shippingAddress->getFirstname();
        $data['receiverLastName'] = $shippingAddress->getLastname();
        $data['receiverPhoneNumber'] = $helper->telephoneNumberCleaning($shippingAddress->getTelephone());
        $data['receiverEmail'] = $shippingAddress->getEmail();
        $data['receiverStreet'] = $street[0];
        $data['receiverBuildingNumber'] = isset($street[1]) ? $street[1] : '';
        $data['receiverFlatNumber'] = '';
        $data['receiverPostCode'] = $shippingAddress->getPostcode();
        $data['receiverCity'] = $shippingAddress->getCity();

        $data['operatorName'] = $shippingAddress->getPosOperator();

        $data['parcel'] = [
            'dimensions' => $this->_getParcelDimensions($helper)
        ];

        $data = $this->_prepareSenderData($data, $helper);

        return $data;
    }

    /**
     * Prepare sender data in fomrat accptable by Bliskapaczka API
     *
     * @param array $data
     * @param Sendit_Bliskapaczka_Helper_Data $helper
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _prepareSenderData($data, Sendit_Bliskapaczka_Helper_Data $helper)
    {
        if (Mage::getStoreConfig($helper::TODOOR_SENDER_EMAIL)) {
            $data['senderEmail'] = Mage::getStoreConfig($helper::TODOOR_SENDER_EMAIL);
        }

        if (Mage::getStoreConfig($helper::TODOOR_SENDER_FIRST_NAME)) {
            $data['senderFirstName'] = Mage::getStoreConfig($helper::TODOOR_SENDER_FIRST_NAME);
        }

        if (Mage::getStoreConfig($helper::TODOOR_SENDER_LAST_NAME)) {
            $data['senderLastName'] = Mage::getStoreConfig($helper::TODOOR_SENDER_LAST_NAME);
        }

        if (Mage::getStoreConfig($helper::TODOOR_SENDER_PHONE_NUMBER)) {
            $data['senderPhoneNumber'] = $helper->telephoneNumberCleaning(
                Mage::getStoreConfig($helper::TODOOR_SENDER_PHONE_NUMBER)
            );
        }

        if (Mage::getStoreConfig($helper::TODOOR_SENDER_STREET)) {
            $data['senderStreet'] = Mage::getStoreConfig($helper::TODOOR_SENDER_STREET);
        }

        if (Mage::getStoreConfig($helper::TODOOR_SENDER_BUILDING_NUMBER)) {
            $data['senderBuildingNumber'] = Mage::getStoreConfig($helper::TODOOR_SENDER_BUILDING_NUMBER);
        }

        if (Mage::getStoreConfig($helper::TODOOR_SENDER_FLAT_NUMBER)) {
            $data['senderFlatNumber'] = Mage::getStoreConfig($helper::TODOOR_SENDER_FLAT_NUMBER);
        }

        if (Mage::getStoreConfig($helper::TODOOR_SENDER_POST_CODE)) {
            $data['senderPostCode'] = Mage::getStoreConfig($helper::TODOOR_SENDER_POST_CODE);
        }

        if (Mage::getStoreConfig($helper::TODOOR_SENDER_CITY)) {
            $data['senderCity'] = Mage::getStoreConfig($helper::TODOOR_SENDER_CITY);
        }

        return $data;
    }
}
