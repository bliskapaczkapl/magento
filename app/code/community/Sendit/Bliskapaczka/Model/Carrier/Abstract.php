<?php

use Bliskapaczka\ApiClient;

/**
 * Abstract class for Bliskapaczka shipping methods
 *
 * @author Mateusz Koszutowski (mkoszutowski@divante.pl)
 */
abstract class Sendit_Bliskapaczka_Model_Carrier_Abstract
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    const SHIPPING_CODE = '';

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
     * Method for calculating items for free shipping.
     * Whole logic copied from Mage_Shipping_Model_Carrier_Flatrate::collectRates
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return int
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _calculateFreeBoxes(Mage_Shipping_Model_Rate_Request $request)
    {
        $freeBoxes = 0;
        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {

                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }

                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        if ($child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
                            $freeBoxes += $item->getQty() * $child->getQty();
                        }
                    }
                } elseif ($item->getFreeShipping()) {
                    $freeBoxes += $item->getQty();
                }
            }
        }

        return $freeBoxes;
    }

    /**
     * Get price list for carrier
     *
     * @return json
     */
    protected function _getPricing() {

    }

    /**
     * @param Mage_Shipping_Model_Rate_Request $request
     *
     * @return bool|false|Mage_Core_Model_Abstract|Mage_Shipping_Model_Rate_Result|null
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        $this->setFreeBoxes($this->_calculateFreeBoxes($request));

        $result = Mage::getModel('shipping/rate_result');

        $priceList = $this->_getPricing();

        // Get Quote
        $quote = false;
        foreach ($request->getAllItems() as $item){
            $quote = $item->getQuote();
            break;
        }

        /* @var $senditHelper Sendit_Bliskapaczka_Helper_Data */
        $senditHelper = new Sendit_Bliskapaczka_Helper_Data();

        if(!empty($priceList)) {
            foreach ($priceList as $operator) {
                if ($operator->availabilityStatus != false) {
                    $shippingPrice = $operator->price->gross;

                    $method = Mage::getModel('shipping/rate_result_method');
                    $method->setCarrier($this->_code);
                    $method->setCarrierTitle($this->getConfigData('title'));

                    $method->setMethod($operator->operatorName);
                    $method->setMethodTitle($operator->operatorName);

                    $method->setPrice($shippingPrice);
                    $method->setCost($shippingPrice);

                    $result->append($method);

                    if (Mage::getStoreConfig(
                        Sendit_Bliskapaczka_Model_Carrier_Bliskapaczka::COD_SWITCH
                    )) {
                        $this->addCODMethodWithOperator($result, $operator, $shippingPrice);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param Mage_Shipping_Model_Rate_Result_Method $result
     * @param                                        $operator
     * @param float                                  $shippingPrice
     */
    protected function addCODMethodWithOperator($result, $operator, $shippingPrice)
    {
        $method = Mage::getModel('shipping/rate_result_method');
        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod($operator->operatorName . '_' . Sendit_Bliskapaczka_Model_Carrier_Bliskapaczka::COD);
        $method->setMethodTitle($operator->operatorName . '_' . Sendit_Bliskapaczka_Model_Carrier_Bliskapaczka::COD);

        $method->setPrice($shippingPrice);
        $method->setCost($shippingPrice);

        $result->append($method);
    }
}
