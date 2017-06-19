<?php

use Bliskapaczka\ApiClient;

/**
 * Bliskapaczka shipping method
 *
 * @author Mateusz Koszutowski (mkoszutowski@divante.pl)
 */
class Sendit_Bliskapaczka_Model_Carrier_Bliskapaczka
    extends Mage_Shipping_Model_Carrier_Abstract
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
     * Check if carrier has shipping tracking option available
     *
     * @return boolean
     */
    public function isTrackingAvailable()
    {
        return true;
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return array($this->_code => $this->getConfigData('name'));
    }

    /**
     * @param Mage_Shipping_Model_Rate_Request $request
     *
     * @return bool|false|Mage_Core_Model_Abstract|Mage_Shipping_Model_Rate_Result|null
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        $result = Mage::getModel('shipping/rate_result');

        $method = Mage::getModel('shipping/rate_result_method');

        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod($this->_code);
        $method->setMethodTitle($this->getConfigData('name'));

        /* @var $senditHelper Sendit_Bliskapaczka_Helper_Data */
        $senditHelper = new Sendit_Bliskapaczka_Helper_Data();
        /* @var $apiClient \Bliskapaczka\ApiClient\Bliskapaczka */
        $apiClient = $senditHelper->getApiClient();
        $priceList = $apiClient->getPricing(
            array("parcels" => array(array('dimensions' => $senditHelper->getParcelDimensions())))
        );
        
        // Get Quote
        $quote = false;
        foreach ($request->getAllItems() as $item){
            $quote = $item->getQuote();
            break;
        }

        // Get shipping price by Bliskapaczka API
        if ($quote && $quote->getShippingAddress()->getPosOperator()) {
            $posOperator = $quote->getShippingAddress()->getPosOperator();
            $shippingPrice = round($senditHelper->getPriceForCarrier(json_decode($priceList), $posOperator), 2);
        } else {
            // Get lowest price by Bliskapaczka API because we don't know which carrier will be chosen
            $shippingPrice = round($senditHelper->getLowestPrice(json_decode($priceList)), 2);
        }

        $method->setPrice($shippingPrice);
        $method->setCost($shippingPrice);

        $result->append($method);

        return $result;
    }
}
