<?php

/**
 * Class Sendit_Bliskapaczka_Block_Adminhtml_Report
 */
class Sendit_Bliskapaczka_Block_Adminhtml_Report extends Mage_Adminhtml_Block_Widget_Form_Container
{

    /**
     * Sendit_Bliskapaczka_Block_Adminhtml_Report constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'report_id';
        $this->_blockGroup = 'sendit_bliskapaczka';
        $this->_controller = 'adminhtml_report';

        $this->_removeButton('reset');
        $this->_removeButton('back');
        $this->_removeButton('save');

        $this->_addButton('generate', array(
            'label'     => Mage::helper('sendit_bliskapaczka')->__('Generate'),
            'onclick'   => 'editForm.submit();',
            'class'     => 'save',
        ), 1);
    }

    /**
     * @return string
     */
    public function getHeaderText()
    {
        return $this->__('Reports');
    }
}
