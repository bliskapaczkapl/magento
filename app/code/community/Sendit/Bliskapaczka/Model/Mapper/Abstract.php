<?php

/**
 * Abstract class mappers
 */
abstract class Sendit_Bliskapaczka_Model_Mapper_Abstract
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
    }

    /**
     * Get parcel dimensions in format accptable by Bliskapaczka API
     *
     * @param Sendit_Bliskapaczka_Helper_Data $helper
     * @return array
     */
    protected function _getParcelDimensions(Sendit_Bliskapaczka_Helper_Data $helper)
    {
        return $helper->getParcelDimensions();
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

    /**
     * Prepare CoD data in fomrat accptable by Bliskapaczka API
     *
     * @param array $data
     * @param Mage_Sales_Model_Order $order
     * @param Sendit_Bliskapaczka_Helper_Data $helper
     * @return array
     */
    protected function _prepareCodData($data, Mage_Sales_Model_Order $order, Sendit_Bliskapaczka_Helper_Data $helper)
    {
        if (strpos($order->getShippingMethod(true)->getMethod(), '_COD') !== false) {
            $grandTotal = (string)round(floatval($order->getGrandTotal()), 2);
            $data['codValue'] = $grandTotal;
            $data['parcel']['insuranceValue'] = $grandTotal;

            if (Mage::getStoreConfig($helper::COD_BANK_ACCOUNT_NUMBER)) {
                $data['codPayoutBankAccountNumber'] = Mage::getStoreConfig($helper::COD_BANK_ACCOUNT_NUMBER);
            }
        }

        return $data;
    }

    /**
     * Get reciver email
     *
     * @param Mage_Customer_Model_Address_Abstract $shippingAddress
     * @return string
     */
    protected function _getReciverEmailAddress(Mage_Customer_Model_Address_Abstract $shippingAddress)
    {
        $receiverEmail = $shippingAddress->getEmail();
        if ($receiverEmail == null) {
            $receiverEmail = $shippingAddress->getOrder()->getCustomerEmail();
        }

        return $receiverEmail;
    }

    /**
     * Prepare reciver mapped data for Bliskapaczka API
     *
     * @param Mage_Customer_Model_Address_Abstract $shippingAddress
     * @param Sendit_Bliskapaczka_Helper_Data $helper
     * @return array
     */
    public function getShippingAddressData(
        Mage_Customer_Model_Address_Abstract $shippingAddress,
        Sendit_Bliskapaczka_Helper_Data $helper
    ) {
        $streetFirstLine = isset($shippingAddress->getStreet()[0]) ? $shippingAddress->getStreet()[0] : '';
        $streetSecondLine = isset($shippingAddress->getStreet()[1]) ? $shippingAddress->getStreet()[1] : '';

        if (true === empty($streetSecondLine)) {
            $fullStreet = $shippingAddress->getStreet()[0];
            $street = preg_split("/\s+(?=\S*+$)/", $fullStreet);

            $streetFirstLine = $street[0];
            $streetSecondLine = isset($street[1]) ? $street[1] : '';
        }

        $data['receiverFirstName'] = $shippingAddress->getFirstname();
        $data['receiverLastName'] = $shippingAddress->getLastname();
        $data['receiverPhoneNumber'] = $helper->telephoneNumberCleaning($shippingAddress->getTelephone());
        $data['receiverEmail'] = $this->_getReciverEmailAddress($shippingAddress);
        $data['receiverStreet'] = $streetFirstLine;
        $data['receiverBuildingNumber'] = $streetSecondLine;
        $data['receiverFlatNumber'] = '';
        $data['receiverPostCode'] = $shippingAddress->getPostcode();
        $data['receiverCity'] = $shippingAddress->getCity();

        return $data;
    }
}
