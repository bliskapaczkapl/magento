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
    const SHIPPING_CODE = 'sendit_bliskapaczka_courier';

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
        $priceList = $apiClient->get(
            array("parcel" => array('dimensions' => $senditHelper->getParcelDimensions()))
        );

        return json_decode($priceList);
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
            }
        }

        return $result;
    }
}
