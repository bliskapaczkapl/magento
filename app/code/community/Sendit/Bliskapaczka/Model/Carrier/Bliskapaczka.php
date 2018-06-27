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
    const SHIPPING_CODE_COD        = 'sendit_bliskapaczka_cod';
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
     * @return json
     */
    public function _getPricing()
    {
        /* @var $senditHelper Sendit_Bliskapaczka_Helper_Data */
        $senditHelper = new Sendit_Bliskapaczka_Helper_Data();
        $priceList = $senditHelper->getPriceList();

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
     * @param Mage_Shipping_Model_Rate_Request $request
     * @param Mage_Shipping_Model_Rate_Result_Method $result
     *
     * @return bool|false|Mage_Core_Model_Abstract|Mage_Shipping_Model_Rate_Result|null
     */
    protected function _collectRatesForAgregated(Mage_Shipping_Model_Rate_Request $request, $result)
    {
        $priceList = $this->_getPricing();

        if ($priceList != new stdClass()) {
            $this->setFreeBoxes($this->_calculateFreeBoxes($request));

            $method = Mage::getModel('shipping/rate_result_method');

            $method->setCarrier($this->_code);
            $method->setCarrierTitle($this->getConfigData('title'));

            $method->setMethod($this->_code);
            $method->setMethodTitle($this->getConfigData('name'));

            // Get Quote
            $quote = false;
            foreach ($request->getAllItems() as $item){
                $quote = $item->getQuote();
                break;
            }

            if ($request->getFreeShipping() === true || $request->getPackageQty() == $this->getFreeBoxes()) {
                $shippingPrice = '0.00';
            } else {
                /* @var $senditHelper Sendit_Bliskapaczka_Helper_Data */
                $senditHelper = new Sendit_Bliskapaczka_Helper_Data();

                // Get shipping price by Bliskapaczka API
                if ($quote && $quote->getShippingAddress()->getPosOperator()) {
                    $posOperator = $quote->getShippingAddress()->getPosOperator();
                    $shippingPrice = round($senditHelper->getPriceForCarrier($priceList, $posOperator), 2);
                } else {
                    // Get lowest price by Bliskapaczka API because we don't know which carrier will be chosen
                    $shippingPrice = round($senditHelper->getLowestPrice($priceList), 2);
                }
            }

            $method->setPrice($shippingPrice);
            $method->setCost($shippingPrice);

            $result->append($method);

            if (Mage::getStoreConfig(
                Sendit_Bliskapaczka_Model_Carrier_Bliskapaczka::COD_SWITCH
            )) {
                $this->addCODMethod($result, $shippingPrice);
            }
        }

        return $result;
    }

    /**
     * @param Mage_Shipping_Model_Rate_Result_Method $result
     * @param float                                  $shippingPrice
     */
    protected function addCODMethod($result, $shippingPrice)
    {
        $method = Mage::getModel('shipping/rate_result_method');

        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod($this->_code . '_' . Sendit_Bliskapaczka_Model_Carrier_Bliskapaczka::COD);
        $method->setMethodTitle(
            $this->getConfigData('name') . ' ' . Sendit_Bliskapaczka_Model_Carrier_Bliskapaczka::COD
        );

        $method->setPrice($shippingPrice);
        $method->setCost($shippingPrice);

        $result->append($method);
    }
}
