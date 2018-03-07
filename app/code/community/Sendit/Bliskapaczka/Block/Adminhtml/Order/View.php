<?php

/**
 * Class Sendit_Bliskapaczka_Block_Adminhtml_Order_View
 */
class Sendit_Bliskapaczka_Block_Adminhtml_Order_View extends Mage_Adminhtml_Block_Sales_Order_View
{
    /**
     * Sendit_Bliskapaczka_Block_Adminhtml_Order_View constructor.
     */
    public function __construct()
    {
        $this->_objectId    = 'order_id';
        $this->_controller  = 'sales_order';
        $this->_mode        = 'view';

        Mage_Adminhtml_Block_Widget_Form_Container::__construct();

        $this->_removeButton('delete');
        $this->_removeButton('reset');
        $this->_removeButton('save');
        $this->setId('sales_order_view');
        $order = $this->getOrder();
        $bliskaOrder = $this->getBliskaOrder();
        $coreHelper = Mage::helper('core');

        if ($this->_isAllowedAction('cancel') && $bliskaOrder->canCancel()) {
            $confirmationMessage = $coreHelper->jsQuoteEscape(
                Mage::helper('sales')->__('Are you sure you want to cancel this order?')
            );
            $this->_addButton('order_cancel', array(
                'label'     => Mage::helper('sales')->__('Cancel'),
                'onclick'   => 'deleteConfirm(\'' . $confirmationMessage . '\', \'' . $this->getCancelUrl() . '\')',
            ));
        }

        if ($this->_isAllowedAction('waybill') && $bliskaOrder->canWaybill()) {
            $this->_addButton('order_waybill', array(
                'label'      => Mage::helper('sales')->__('Waybill'),
                'formtarget' => '_blank',
                'onclick'    => 'window.open(\'' . $this->getWaybillUrl() . '\', \'_blank\')',
            ));
        }

        if ($this->_isAllowedAction('get')) {
            $confirmationMessage = $coreHelper->jsQuoteEscape(
                Mage::helper('sales')->__('Are you sure you want to update this order?')
            );

            $this->_addButton('order_get', array(
                'label'      => Mage::helper('sales')->__('Get'),
                'formtarget' => '_blank',
                'onclick'   => 'deleteConfirm(\'' . $confirmationMessage . '\', \'' . $this->getGetUrl() . '\')',
            ));
        }
    }

    /**
     * Retrieve Bliskapaczka order model object
     *
     * @return Sendit_Bliskapaczka_Model_Order
     */
    public function getBliskaOrder()
    {
        return Mage::registry('bliska_order');
    }

    /**
     * @return string
     */
    public function getHeaderText()
    {
        if ($_extOrderId = $this->getOrder()->getExtOrderId()) {
            $_extOrderId = '[' . $_extOrderId . '] ';
        } else {
            $_extOrderId = '';
        }
        return Mage::helper('sales')->__('Bliskapaczka Order # %s %s | %s', $this->getOrder()->getRealOrderId(), $_extOrderId, $this->formatDate($this->getOrder()->getCreatedAtDate(), 'medium', true));
    }

    /**
     * @return string
     */
    public function getCancelUrl()
    {
        return $this->getUrl('*/*/cancel', array('bliska_order_id' => $this->getRequest()->getParam('bliska_order_id')));
    }

    /**
     * @return string
     */
    public function getGetUrl()
    {
        return $this->getUrl('*/*/get', array('bliska_order_id' => $this->getRequest()->getParam('bliska_order_id')));
    }

    /**
     * @return string
     */
    public function getWaybillUrl()
    {
        return $this->getUrl('*/*/waybill', array('bliska_order_id' => $this->getRequest()->getParam('bliska_order_id')));
    }
}