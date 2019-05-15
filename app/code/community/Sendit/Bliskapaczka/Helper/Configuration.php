<?php

class Sendit_Bliskapaczka_Helper_Configuration extends Mage_Core_Helper_Data
{

    const DEFAULT_GOOGLE_API_KEY = 'AIzaSyCUyydNCGhxGi5GIt5z5I-X6hofzptsRjE';

    const PARCEL_SIZE_TYPE_XML_PATH = 'carriers/sendit_bliskapaczka/parcel_size_type';
    const PARCEL_TYPE_FIXED_SIZE_X_XML_PATH = 'carriers/sendit_bliskapaczka/parcel_size_type_fixed_size_x';
    const PARCEL_TYPE_FIXED_SIZE_Y_XML_PATH = 'carriers/sendit_bliskapaczka/parcel_size_type_fixed_size_y';
    const PARCEL_TYPE_FIXED_SIZE_Z_XML_PATH = 'carriers/sendit_bliskapaczka/parcel_size_type_fixed_size_z';
    const PARCEL_TYPE_FIXED_SIZE_WEIGHT_XML_PATH = 'carriers/sendit_bliskapaczka/parcel_size_type_fixed_size_weight';
    const PARCEL_DEFAULT_SIZE_X = 12;
    const PARCEL_DEFAULT_SIZE_Y = 12;
    const PARCEL_DEFAULT_SIZE_Z = 16;
    const PARCEL_DEFAULT_SIZE_WEIGHT = 1;
    const COD_BANK_ACCOUNT_NUMBER = 'carriers/sendit_bliskapaczka/cod_bank_account_number';

    const SENDER_EMAIL = 'carriers/sendit_bliskapaczka/sender_email';
    const SENDER_FIRST_NAME = 'carriers/sendit_bliskapaczka/sender_first_name';
    const SENDER_LAST_NAME = 'carriers/sendit_bliskapaczka/sender_last_name';
    const SENDER_PHONE_NUMBER = 'carriers/sendit_bliskapaczka/sender_phone_number';
    const SENDER_STREET = 'carriers/sendit_bliskapaczka/sender_street';
    const SENDER_BUILDING_NUMBER = 'carriers/sendit_bliskapaczka/sender_building_number';
    const SENDER_FLAT_NUMBER = 'carriers/sendit_bliskapaczka/sender_flat_number';
    const SENDER_POST_CODE = 'carriers/sendit_bliskapaczka/sender_post_code';
    const SENDER_CITY = 'carriers/sendit_bliskapaczka/sender_city';

    const API_KEY_XML_PATH = 'carriers/sendit_bliskapaczka/bliskapaczkaapikey';
    const API_TEST_MODE_XML_PATH = 'carriers/sendit_bliskapaczka/test_mode';
    const API_AUTO_ADVICE_XML_PATH = 'carriers/sendit_bliskapaczka/auto_advice';

    const GOOGLE_MAP_API_KEY_XML_PATH = 'carriers/sendit_bliskapaczka/google_map_api_key';

    const SLOW_STATUSES = array('READY_TO_SEND', 'POSTED', 'ON_THE_WAY', 'READY_TO_PICKUP', 'OUT_FOR_DELIVERY',
        'REMINDER_SENT', 'PICKUP_EXPIRED', 'AVIZO', 'RETURNED', 'OTHER', 'MARKED_FOR_CANCELLATION');
    const FAST_STATUSES = array('SAVED', 'WAITING_FOR_PAYMENT', 'PAYMENT_CONFIRMED', 'PAYMENT_REJECTED',
        'PAYMENT_CANCELLATION_ERROR', 'PROCESSING', 'ADVISING', 'ERROR');

    const LOG_FILE = 'sendit.log';

    /**
     * Wrapper on Mage::getStoreConfig
     * We don't able to mock static method
     *
     * @param string $config
     * @return mixed
     */
    public function getStoreConfigWrapper($config)
    {
        return Mage::getStoreConfig($config);
    }

    /**
     * Get parcel dimensions in format accptable by Bliskapaczka API
     *
     * @param string $type
     * @return array
     */
    public function getParcelDimensions($type = 'fixed')
    {
        switch ($type) {
            case 'default':
                $height = self::PARCEL_DEFAULT_SIZE_X;
                $length = self::PARCEL_DEFAULT_SIZE_Y;
                $width = self::PARCEL_DEFAULT_SIZE_Z;
                $weight = self::PARCEL_DEFAULT_SIZE_WEIGHT;
                break;

            default:
                $height = $this->getStoreConfigWrapper(self::PARCEL_TYPE_FIXED_SIZE_X_XML_PATH);
                $length = $this->getStoreConfigWrapper(self::PARCEL_TYPE_FIXED_SIZE_Y_XML_PATH);
                $width = $this->getStoreConfigWrapper(self::PARCEL_TYPE_FIXED_SIZE_Z_XML_PATH);
                $weight = $this->getStoreConfigWrapper(self::PARCEL_TYPE_FIXED_SIZE_WEIGHT_XML_PATH);
                break;
        }

        $dimensions = array(
            "height" => $height,
            "length" => $length,
            "width" => $width,
            "weight" => $weight
        );

        return $dimensions;
    }

    /**
     * Get Google API key. If key is not defined return default.
     *
     * @return string
     */
    public function getGoogleMapApiKey()
    {
        $googleApiKey = self::DEFAULT_GOOGLE_API_KEY;

        if (Mage::getStoreConfig(self::GOOGLE_MAP_API_KEY_XML_PATH)) {
            $googleApiKey = Mage::getStoreConfig(self::GOOGLE_MAP_API_KEY_XML_PATH);
        }

        return $googleApiKey;
    }

    /**
     * Get API mode
     *
     * @param string $configValue
     * @return string
     */
    public function getApiMode($configValue = null)
    {

        return ($configValue == '1') ? 'test' : 'prod';
    }

}
