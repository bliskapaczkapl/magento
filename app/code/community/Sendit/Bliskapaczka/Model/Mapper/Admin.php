<?php

/**
 * Class to map module config to data for validator
 */
class Sendit_Bliskapaczka_Model_Mapper_Admin
{
    /**
     * Prepare mapped data for Bliskapaczka API
     *
     * @param array $postData
     * @param Sendit_Bliskapaczka_Helper_Data $helper
     * @return array
     */
    public function getData(array $postData, Sendit_Bliskapaczka_Helper_Data $helper)
    {
        $data = [];

        $data['senderEmail'] = $postData['fields']['sender_email']['value'];
        $data['senderFirstName'] = $postData['fields']['sender_first_name']['value'];
        $data['senderLastName'] = $postData['fields']['sender_last_name']['value'];
        $data['senderPhoneNumber'] = $helper
                                        ->telephoneNumberCleaning($postData['fields']['sender_phone_number']['value']);
        $data['senderStreet'] = $postData['fields']['sender_street']['value'];
        $data['senderBuildingNumber'] = $postData['fields']['sender_building_number']['value'];
        $data['senderFlatNumber'] = $postData['fields']['sender_flat_number']['value'];
        $data['senderPostCode'] = $postData['fields']['sender_post_code']['value'];
        $data['senderCity'] = $postData['fields']['sender_city']['value'];

        $data['codPayoutBankAccountNumber'] = $postData['fields']['cod_bank_account_number']['value'];

        return $data;
    }
}
