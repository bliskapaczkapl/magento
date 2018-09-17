<?php

use Bliskapaczka\ApiClient;

/**
 * Bliskapaczka API helper
 *
 * @author Mateusz Koszutowski (mkoszutowski@divante.pl)
 */
class Sendit_Bliskapaczka_Helper_Api extends Mage_Core_Helper_Data
{

    /**
     * For choosen orders create string with order numbers to get data from API
     * @return string
     */
    public function prepareDataForMassActionReport()
    {
        $entityIds = $this->_getRequest()->getParam('entity_id');

        $bliskaOrderCollection = Mage::getModel('sendit_bliskapaczka/order')->getCollection();

        if ($entityIds) {
            $bliskaOrderCollection->addFieldToSelect('*');
            $bliskaOrderCollection->addFieldToFilter('entity_id', array('in' => $entityIds));
        }

        $numbers = '';
        foreach ($bliskaOrderCollection as $bliskaOrder) {
            if ($numbers && $bliskaOrder->getNumber()) {
                $numbers .= ',' . $bliskaOrder->getNumber();
            } else {
                $numbers = $bliskaOrder->getNumber();
            }
        }

        return $numbers;
    }

    /**
     * Get Bliskapaczka API Client
     *
     * @param Sendit_Bliskapaczka_Helper_Data $senditHelper
     * @return \Bliskapaczka\ApiClient\Bliskapaczka
     */
    public function getApiClientPos($senditHelper)
    {
        $apiClient = new \Bliskapaczka\ApiClient\Bliskapaczka\Pos(
            Mage::getStoreConfig($senditHelper::API_KEY_XML_PATH),
            $senditHelper->getApiMode(Mage::getStoreConfig($senditHelper::API_TEST_MODE_XML_PATH))
        );

        return $apiClient;
    }

    /**
     * Get Bliskapaczka API Client
     *
     * @param string $method
     * @param Sendit_Bliskapaczka_Helper_Data $senditHelper
     * @param bool $advice
     * @return mixed
     */
    public function getApiClientForOrder($method, $senditHelper, $advice = false) {
        if (!$advice) {
            $advice = Mage::getStoreConfig(self::API_AUTO_ADVICE_XML_PATH);
        }

        $methodName = $this->getApiClientForOrderMethodName($method, $advice, $senditHelper);

        return $this->{$methodName}();
    }

    /**
     * Get method name to bliskapaczka api client create order action
     *
     * @param string $method
     * @param string $autoAdvice
     * @param Sendit_Bliskapaczka_Helper_Data $senditHelper
     * @return string
     */
    public function getApiClientForOrderMethodName($method, $autoAdvice, $senditHelper)
    {
        $type = 'Todoor';

        if ($senditHelper->isPoint($method)) {
            $type = 'Order';
        }

        $methodName = 'getApiClient' . $type;

        if ($autoAdvice) {
            $methodName .= 'Advice';
        }

        return $methodName;
    }
}
