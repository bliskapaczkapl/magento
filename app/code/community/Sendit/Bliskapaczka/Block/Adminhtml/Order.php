<?php

/**
 * Class Sendit_Bliskapaczka_Block_Adminhtml_Order
 */
class Sendit_Bliskapaczka_Block_Adminhtml_Order extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Sendit_Bliskapaczka_Block_Adminhtml_Order constructor.
     */
    public function __construct()
    {
        $this->_blockGroup = 'sendit_bliskapaczka';
        $this->_controller = 'adminhtml_order';
        $this->_headerText = $this->__('Order');

        parent::__construct();
        $this->_removeButton('add');

        $this->_addButton("Confirm", array(
            "label" => Mage::helper("core")->__("Close Poczta Polska buffer"),
            "onclick" => "location.href = '" . $this->getConfirmUrl() . "';",
            "class" => "btn btn-danger",
        ));
    }

    /**
     * @return string
     */
    public function getConfirmUrl()
    {
        return $this->getUrl(
            '*/*/confirm'
        );
    }
}
