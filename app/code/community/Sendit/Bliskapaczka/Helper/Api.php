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
}
