<?php

class Sendit_Bliskapaczka_Block_Adminhtml_Order extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'sendit_bliskapaczka';
        $this->_controller = 'adminhtml_order';
        $this->_headerText = $this->__('Order');

        parent::__construct();
        $this->_removeButton('add');
    }
}