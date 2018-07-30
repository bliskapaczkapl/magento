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
    const SHIPPING_CODE            = 'sendit_bliskapaczka';
    const COD                      = 'COD';
    const NEODYNAMIC_LICENSE_OWNER = 'carriers/sendit_bliskapaczka/neodinamic_license_owner';
    const NEODYNAMIC_LICENSE_KEY   = 'carriers/sendit_bliskapaczka/neodinamic_license_owner';
    const NEODYNAMIC_PRINT         = 'carriers/sendit_bliskapaczka/neodinamic_print';
    const COD_SWITCH               = 'carriers/sendit_bliskapaczka/cod';

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
     *
     * @return json
     */
    public function _getPricing($cod = null)
    {
        /* @var $senditHelper Sendit_Bliskapaczka_Helper_Data */
        $senditHelper = new Sendit_Bliskapaczka_Helper_Data();
        $priceList = $senditHelper->getPriceList($cod);

        return $priceList;
    }

    /**
     * @param Mage_Shipping_Model_Rate_Request $request
     *
     * @return bool|false|Mage_Core_Model_Abstract|Mage_Shipping_Model_Rate_Result|null
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        $result = parent::collectRates($request);
        $this->_collectRatesForAgregated($request, $result);

        return $result;
    }

    /**
     * Add agregated bliskapaczka shipping method
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @param Mage_Shipping_Model_Rate_Result_Method $result
     *
     * @return bool|false|Mage_Core_Model_Abstract|Mage_Shipping_Model_Rate_Result|null
     */
    protected function _collectRatesForAgregated(Mage_Shipping_Model_Rate_Request $request, $result)
    {
        /* @var $senditHelper Sendit_Bliskapaczka_Helper_Data */
        $senditHelper = new Sendit_Bliskapaczka_Helper_Data();

        $operator = new stdClass();
        $operator->operatorName = $this->_code;

        $priceList = $this->_getPricing();
        if (!empty($priceList)) {
            $shippingPrice = $this->_shippingPriceForAgregated($senditHelper, $request, $priceList);
            $this->_addShippingMethod($result, $operator, false, $senditHelper, $shippingPrice);
        }

        if (Mage::getStoreConfig(Sendit_Bliskapaczka_Model_Carrier_Bliskapaczka::COD_SWITCH)) {
            $priceList = $this->_getPricing(true);
            if (!empty($priceList)) {
                $shippingPrice = $this->_shippingPriceForAgregated($$senditHelper, $request, $priceList);
                $this->_addShippingMethod($result, $operator, true, $senditHelper, $shippingPrice);
            }
        }

        return $result;
    }

    /**
     * Calculate shipping price for agregated bliskapaczka shipping method
     *
     * @param Sendit_Bliskapaczka_Helper_Data $senditHelper
     * @param Mage_Shipping_Model_Rate_Request $request
     * @param json $priceList
     *
     * @return float
     */
    protected function _shippingPriceForAgregated($senditHelper, $request, $priceList)
    {
        // Get Quote
        $quote = false;
        foreach ($request->getAllItems() as $item){
            $quote = $item->getQuote();
            break;
        }

        if ($request->getFreeShipping() === true || $request->getPackageQty() == $this->getFreeBoxes()) {
            $shippingPrice = '0.00';
        } else {
            // Get shipping price by Bliskapaczka API
            if ($quote && $quote->getShippingAddress()->getPosOperator()) {
                $posOperator = $quote->getShippingAddress()->getPosOperator();
                $shippingPrice = round($senditHelper->getPriceForCarrier($priceList, $posOperator), 2);
            } else {
                // Get lowest price by Bliskapaczka API because we don't know which carrier will be chosen
                $shippingPrice = round($senditHelper->getLowestPrice($priceList), 2);
            }
        }

        return $shippingPrice;
    }
}
