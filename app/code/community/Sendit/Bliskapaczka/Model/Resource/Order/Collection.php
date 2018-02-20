<?php

class Divante_Complaint_Model_Resource_Ticket_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('sendit_bliskapaczka/order');
    }
}