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
     * @return array
     */
    public function getData(Mage_Sales_Model_Order $order, Sendit_Bliskapaczka_Helper_Data $helper)
    {
        $data = [];

        $shippingAddress = $order->getShippingAddress();

        $data['receiverFirstName'] = $shippingAddress->getFirstname();
        $data['receiverLastName'] = $shippingAddress->getLastname();
        $data['receiverPhoneNumber'] = $helper->telephoneNumberCleaning($shippingAddress->getTelephone());
        $data['receiverEmail'] = $shippingAddress->getEmail();

        $data['operatorName'] = $shippingAddress->getPosOperator();
        $data['destinationCode'] = $shippingAddress->getPosCode();

        $data['additionalInformation'] = $order->getIncrementId();

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
        if (Mage::getStoreConfig($helper::SENDER_EMAIL)) {
            $data['senderEmail'] = Mage::getStoreConfig($helper::SENDER_EMAIL);
        }

        if (Mage::getStoreConfig($helper::SENDER_FIRST_NAME)) {
            $data['senderFirstName'] = Mage::getStoreConfig($helper::SENDER_FIRST_NAME);
        }

        if (Mage::getStoreConfig($helper::SENDER_LAST_NAME)) {
            $data['senderLastName'] = Mage::getStoreConfig($helper::SENDER_LAST_NAME);
        }

        if (Mage::getStoreConfig($helper::SENDER_PHONE_NUMBER)) {
            $data['senderPhoneNumber'] = $helper->telephoneNumberCleaning(
                Mage::getStoreConfig($helper::SENDER_PHONE_NUMBER)
            );
        }

        if (Mage::getStoreConfig($helper::SENDER_STREET)) {
            $data['senderStreet'] = Mage::getStoreConfig($helper::SENDER_STREET);
        }

        if (Mage::getStoreConfig($helper::SENDER_BUILDING_NUMBER)) {
            $data['senderBuildingNumber'] = Mage::getStoreConfig($helper::SENDER_BUILDING_NUMBER);
        }

        if (Mage::getStoreConfig($helper::SENDER_FLAT_NUMBER)) {
            $data['senderFlatNumber'] = Mage::getStoreConfig($helper::SENDER_FLAT_NUMBER);
        }

        if (Mage::getStoreConfig($helper::SENDER_POST_CODE)) {
            $data['senderPostCode'] = Mage::getStoreConfig($helper::SENDER_POST_CODE);
        }

        if (Mage::getStoreConfig($helper::SENDER_CITY)) {
            $data['senderCity'] = Mage::getStoreConfig($helper::SENDER_CITY);
        }

        return $data;
    }
}
