<?php

/**
 * Class Sendit_Bliskapaczka_Model_Order
 */
class Sendit_Bliskapaczka_Model_Order extends Mage_Core_Model_Abstract
{
    /**
     * Model constructor
     */
    public function _construct()
    {
        $this->_init('sendit_bliskapaczka/order');
    }
}