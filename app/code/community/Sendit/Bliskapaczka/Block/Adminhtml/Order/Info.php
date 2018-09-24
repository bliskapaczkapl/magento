<?php

/**
 * Class Sendit_Bliskapaczka_Block_Adminhtml_Order_Info
 */
class Sendit_Bliskapaczka_Block_Adminhtml_Order_Info extends Mage_Adminhtml_Block_Template
{
    /**
     * @return Sendit_Bliskapaczka_Model_Order
     */
    public function getBliskaOrder()
    {
        $bliskaOrder = Mage::registry('bliska_order');

        return $bliskaOrder;
    }
}
