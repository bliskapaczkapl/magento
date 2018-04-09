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
}
