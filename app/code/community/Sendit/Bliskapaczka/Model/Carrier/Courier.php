<?php

use Bliskapaczka\ApiClient;

/**
 * Bliskapaczka Courier shipping method
 *
 * @author Mateusz Koszutowski (mkoszutowski@divante.pl)
 */
class Sendit_Bliskapaczka_Model_Carrier_Courier extends Sendit_Bliskapaczka_Model_Carrier_Abstract
implements Mage_Shipping_Model_Carrier_Interface
{
    const SHIPPING_CODE     = 'sendit_bliskapaczka_courier';

    /**
     * Carrier's code
     *
     * @var string
     */
    protected $_code = self::SHIPPING_CODE;

    /**
     * Get price list for carrier
     *
     * @param boot $cod
     * @param string $type
     *
     * @return json
     */
    public function _getPricing($cod = null, $type = 'fixed')
    {
        /* @var $senditHelper Sendit_Bliskapaczka_Helper_Data */
        $senditHelper = new Sendit_Bliskapaczka_Helper_Data();
        /* @var $apiClient \Bliskapaczka\ApiClient\Bliskapaczka */
        $apiClient = $senditHelper->getApiClientPricing();

        $D2DData = array(
            "parcel" => array('dimensions' => $senditHelper->getParcelDimensions($type)),
            "deliveryType" => "D2D"
        );
        if ($cod) {
            $D2DData['codValue'] = 1;
        }

        $P2DData = array(
            "parcel" => array('dimensions' => $senditHelper->getParcelDimensions($type)),
            "deliveryType" => "P2D"
        );
        if ($cod) {
            $P2DData['codValue'] = 1;
        }

        try {
            $D2DPriceList = $apiClient->get($D2DData);
            $P2DPriceList = $apiClient->get($P2DData);
        } catch (Exception $e) {
            $D2DPriceList = $P2DPriceList = '{}';
            Mage::log($e->getMessage(), null, Sendit_Bliskapaczka_Helper_Data::LOG_FILE);
        }

        $P2DPriceListArray = json_decode($P2DPriceList);
        $P2DPriceListArray[0]->operatorName = "POCZTA_P2D";

        return array_merge(json_decode($D2DPriceList), $P2DPriceListArray);
    }
}
