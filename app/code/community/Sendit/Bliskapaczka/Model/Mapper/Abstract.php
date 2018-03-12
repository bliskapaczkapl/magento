<?php

/**
 * Abstract class mappers
 */
abstract class  Sendit_Bliskapaczka_Model_Mapper_Abstract
{

    /**
     * Prepare mapped data for Bliskapaczka API
     *
     * @param Mage_Sales_Model_Order $order
     * @param Sendit_Bliskapaczka_Helper_Data $helper
     * @return array
     */
    public function getData(Mage_Sales_Model_Order $order, Sendit_Bliskapaczka_Helper_Data $helper)
    {

    }

    /**
     * Get parcel dimensions in format accptable by Bliskapaczka API
     *
     * @param Sendit_Bliskapaczka_Helper_Data $helper
     * @return array
     */
    protected function _getParcelDimensions(Sendit_Bliskapaczka_Helper_Data $helper)
    {
        return $helper->getParcelDimensions();
    }
}
