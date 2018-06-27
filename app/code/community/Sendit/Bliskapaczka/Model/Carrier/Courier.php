<?php

use Bliskapaczka\ApiClient;

/**
 * Bliskapaczka Courier shipping method
 *
 * @author Mateusz Koszutowski (mkoszutowski@divante.pl)
 */
class Sendit_Bliskapaczka_Model_Carrier_Courier
    extends Sendit_Bliskapaczka_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    const SHIPPING_CODE     = 'sendit_bliskapaczka_courier';
    const SHIPPING_CODE_COD = 'sendit_bliskapaczka_courier_cod';

    /**
     * Carrier's code
     *
     * @var string
     */
    protected $_code = self::SHIPPING_CODE;

	/**
     * Get price list for carrier
     *
     * @return json
     */
    public function _getPricing()
    {
        /* @var $senditHelper Sendit_Bliskapaczka_Helper_Data */
        $senditHelper = new Sendit_Bliskapaczka_Helper_Data();
        /* @var $apiClient \Bliskapaczka\ApiClient\Bliskapaczka */
        $apiClient = $senditHelper->getApiClientPricingTodoor();

        try {
            $priceList = $apiClient->get(
                array("parcel" => array('dimensions' => $senditHelper->getParcelDimensions()))
            );
        } catch (Exception $e) {
            $priceList = '{}';
            Mage::log($e->getMessage(), null, Sendit_Bliskapaczka_Helper_Data::LOG_FILE);
        }

        return json_decode($priceList);
    }

}
