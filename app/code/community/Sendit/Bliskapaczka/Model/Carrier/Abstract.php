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
        $priceList = $this->_getPricing();
        $allowedShippingMethod = array();
        foreach ($priceList as $operator) {
            $allowedShippingMethod[$operator->operatorName] = $operator->operatorFullName;
            $allowedShippingMethod[$operator->operatorName . '_COD'] = $operator->operatorFullName . ' COD';
        }

        $allowedShippingMethod['sendit_bliskapaczka'] = 'bliskapaczka.pl';

        return $allowedShippingMethod;
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
     * @param boot $cod
     *
     * @return json
     */
    public function _getPricing($cod = null)
    {
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
        $cartRulesForAgregated = $this->_getCartRulesForAggregatedOperator($this->_code);

        /* @var $senditHelper Sendit_Bliskapaczka_Helper_Data */
        $senditHelper = new Sendit_Bliskapaczka_Helper_Data();

        $priceList = $this->_getPricing();
        if (!empty($priceList)) {
            foreach ($priceList as $operator) {
                if ($operator->availabilityStatus != false) {
                    $rules = array(
                        'single' => $this->_getCartRulesForSingeOperator($this->_code, $operator, false),
                        'aggregated' => $cartRulesForAgregated
                    );

                    $shippingPrice = $this->_shippingPrice($operator, $request, $rules, false);
                    $this->_addShippingMethod($result, $operator, false, $senditHelper, $shippingPrice);
                }
            }
        }

        if (Mage::getStoreConfig(Sendit_Bliskapaczka_Model_Carrier_Bliskapaczka::COD_SWITCH)) {
            $priceListCod = $this->_getPricing(true);

            if (!empty($priceListCod)) {
                foreach ($priceListCod as $operator) {
                    if ($operator->availabilityStatus != false) {
                        $rules = array(
                            'single' => $this->_getCartRulesForSingeOperator($this->_code, $operator, true),
                            'aggregated' => $cartRulesForAgregated
                        );

                        $shippingPrice = $this->_shippingPrice($operator, $request, $rules, true);
                        $this->_addShippingMethod($result, $operator, true, $senditHelper, $shippingPrice);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Get cart rules for single shipping method
     *
     * @param string $code
     * @param string $operator
     * @param bool $cod
     */
    protected function _getCartRulesForSingeOperator($code, $operator, $cod)
    {
        $rules = Mage::getModel('salesrule/rule')
            ->getCollection()
            ->addFieldToFilter('simple_free_shipping', array('eq' => Mage_SalesRule_Model_Rule::FREE_SHIPPING_ADDRESS))
            ->addFieldToFilter(
                'conditions_serialized',
                array('like' => '%"' . $code . '_' . $operator->operatorName . ($cod ? '_COD' : '') . '"%')
            )
            ->addFieldToFilter('is_active', array('eq' => '1'));

        return $rules;
    }

    /**
     * Get cart rules for aggregated shipping method
     *
     * @param string $code
     */
    protected function _getCartRulesForAggregatedOperator($code)
    {
        $rules = Mage::getModel('salesrule/rule')
            ->getCollection()
            ->addFieldToFilter('simple_free_shipping', array('eq' => Mage_SalesRule_Model_Rule::FREE_SHIPPING_ADDRESS))
            ->addFieldToFilter(
                'conditions_serialized',
                array(
                    'like' => '%"' . $code . '_' . Sendit_Bliskapaczka_Model_Carrier_Bliskapaczka::SHIPPING_CODE . '"%'
                )
            )
            ->addFieldToFilter('is_active', array('eq' => '1'));

        return $rules;
    }

    /**
     * Set shipping method for operator
     *
     * @param Mage_Shipping_Model_Rate_Result_Method $result
     * @param string $operator
     * @param bool $cod
     * @param Sendit_Bliskapaczka_Helper_Data $senditHelper
     * @param float $shippingPrice
     */
    protected function _addShippingMethod($result, $operator, $cod, $senditHelper, $shippingPrice)
    {
        if ($this->_code != $operator->operatorName) {
            $methodName = $methodTitle = $operator->operatorName;
        } else {
            $methodName = $this->_code;
            $methodTitle = '';
        }

        if ($cod) {
            $methodName .= '_' . Sendit_Bliskapaczka_Model_Carrier_Bliskapaczka::COD;
            $methodTitle .= (($methodTitle) ? ' - ' : '') . $senditHelper->__('Cash on Delivery');
        }

        $method = Mage::getModel('shipping/rate_result_method');
        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod($methodName);
        $method->setMethodTitle($methodTitle);

        $method->setPrice($shippingPrice);
        $method->setCost($shippingPrice);

        $result->append($method);
    }

    /**
     * Calculate shipping price for bliskapaczka shipping method
     *
     * @param stdClass $operator
     * @param Mage_Shipping_Model_Rate_Request $request
     * @param array $rules
     * @param bool $cod
     *
     * @return float
     */
    protected function _shippingPrice($operator, $request, $rules, $cod = false)
    {
        $price = $operator->price->gross;
        $firstItem = false;
        foreach ($request->getAllItems() as $item) {
            $firstItem = $item;
            break;
        }

        if ($request->getPackageQty() == $this->getFreeBoxes()) {
            $price = 0.00;
        }

        $price = $this->_validateCartRule(
            $rules['aggregated'],
            $this->_code . '_' . Sendit_Bliskapaczka_Model_Carrier_Bliskapaczka::SHIPPING_CODE,
            $price,
            $item
        );
        $price = $this->_validateCartRule(
            $rules['single'],
            $this->_code . '_' . $operator->operatorName . ($cod ? '_COD' : ''),
            $price,
            $item
        );

        return $price;
    }

    /**
     * Check if we have some cart rules for shipping method
     *
     * @param Mage_SalesRule_Model_Resource_Rule_Collection $rules
     * @param string $conditionValue
     * @param float $price
     * @param Mage_Sales_Model_Quote_Item $item
     *
     * @return float
     */
    protected function _validateCartRule($rules, $conditionValue, $price, $item)
    {
        foreach ($rules as $index => $rule) {
            $newRule = clone $rule;
            $conditions = unserialize($newRule->getConditionsSerialized());
            foreach ($conditions['conditions'] as $index => $condition) {
                if ($condition['value'] == $conditionValue) {
                    unset($conditions['conditions'][$index]);
                }
            }

            $newRule->setConditionsSerialized(serialize($conditions));
            if ($newRule->getConditions()->validate($item)) {
                $price = 0.00;
            }
        }

        return $price;
    }
}
