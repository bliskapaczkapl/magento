<?php

use Bliskapaczka\ApiClient;

/**
 * Bliskapaczka shipping method
 *
 * @author Mateusz Koszutowski (mkoszutowski@divante.pl)
 */
class Sendit_Bliskapaczka_Model_Carrier_Bliskapaczka
    extends Sendit_Bliskapaczka_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    const SHIPPING_CODE = 'sendit_bliskapaczka';

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
        $apiClient = $senditHelper->getApiClientPricing();
        $priceList = $apiClient->get(
            array("parcel" => array('dimensions' => $senditHelper->getParcelDimensions()))
        );

        return $priceList;
    }
}
