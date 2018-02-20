<?php

class Sendit_Bliskapaczka_Model_Resource_Order extends Mage_Core_Model_Resource_Db_Abstract {

    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init('sendit_bliskapaczka/order', 'entity_id');
    }
}