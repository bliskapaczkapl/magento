<?php

class Sendit_Bliskapaczka_Helper_Price extends Mage_Core_Helper_Data
{

    /**
     * Get lowest price from pricing list
     *
     * @param array $priceList
     * @param array $allRates
     * @param boot $cod
     * @return float
     */
    public function getLowestPrice($priceList, $allRates, $cod = false)
    {
        $lowestPrice = null;
        $cod = ($cod ? '_COD' : '');
        $rates = array();
        foreach ($allRates as $rate) {
            $rates[$rate->getCode()] = $rate;
        }

        foreach ($priceList as $carrier) {
            if ($carrier->availabilityStatus == false
                || !isset($rates['sendit_bliskapaczka_' . $carrier->operatorName . $cod])
            ) {
                continue;
            }

            $price = $this->_getPriceWithCartRules($carrier, $rates, $cod);

            if ($lowestPrice == null || $lowestPrice > $price) {
                $lowestPrice = $price;
            }
        }

        return $lowestPrice;
    }

    /**
     * Get price for specific carrier
     *
     * @param array $priceList
     * @param array $allRates
     * @param string $carrierName
     * @param boot $cod
     * @return float|false
     */
    public function getPriceForCarrier($priceList, $allRates, $carrierName, $cod = false)
    {
        $rates = array();
        $cod = ($cod ? '_COD' : '');
        foreach ($allRates as $rate) {
            $code = $rate->getCode();
            if (is_null($code)) {
                $code = $rate->getCarrier() . '_' . $rate->getMethod() . $cod;
            }
            $rates[$code] = $rate;
        }
        foreach ($priceList as $carrier) {
            if ($carrier->operatorName == $carrierName && $rates['sendit_bliskapaczka_' . $carrierName . $cod]) {
                return $this->_getPriceWithCartRules($carrier, $rates, $cod);
            }
        }
        return false;
    }

    /**
     * Get price with applied cart rules
     *
     * @param sdtClass $carrier
     * @param array $rates
     * @param string $cod
     * @return float
     */
    public function _getPriceWithCartRules($carrier, $rates, $cod)
    {
        $price = $carrier->price->gross;
        $priceFromMagento = $rates['sendit_bliskapaczka_' . $carrier->operatorName . $cod]->getPrice();
        $price = $priceFromMagento < $price ? $priceFromMagento : $price;

        return $price;
    }
}
