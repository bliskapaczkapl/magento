<?php

/**
 * Class Sendit_Bliskapaczka_Block_Adminhtml_Report_Form
 */
class Sendit_Bliskapaczka_Block_Adminhtml_Report_Form extends Mage_Adminhtml_Block_Widget_Form //implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    /**
     * Sendit_Bliskapaczka_Block_Adminhtml_Report_Form constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('report_form');
        $this->setTitle($this->__('Report'));
    }

    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form([
            'id'     => 'edit_form',
            'action' => $this->getUrl('*/*/generate'),
            'method' => 'post',
            'name'   => 'editForm',
            'enctype' => 'multipart/form-data'
        ]);
        $form->setUseContainer(true);
        $this->setForm($form);

        $this->_prepareServiceFieldset($form);

        return parent::_prepareForm();
    }

    /**
     * @return Mage_Adminhtml_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('adminhtml/session');
    }

    /**
     * Generates complaint fieldset with inner items
     *
     * @param \Varien_Data_Form $form
     *
     * @throws \Exception
     */
    protected function _prepareServiceFieldset(Varien_Data_Form $form)
    {
        $operators = $this->getOperators();

        if (!empty($operators)) {
            foreach ($operators as $operator) {

                $fieldset = $form->addFieldset(
                    $operator,
                    [
                        'legend' => $this->__($operator),
                        'class'  => 'fieldset-wide',
                    ]
                );

                $fieldset->addField(
                    $operator . '_radio',
                    'radio',
                    array(
                        'label'    => Mage::helper('sendit_bliskapaczka')->__('Choose'),
                        'name'     => 'operator',
                        'onclick'  => "",
                        'onchange' => "",
                        'value'    => $operator,
                        'disabled' => false,
                        'readonly' => false,
                        //'after_element_html' => '<small>Comments</small>',
                        'tabindex' => 1
                    )
                );

                $fieldset->addField(
                    $operator . '_date_from',
                    'datetime',
                    array(
                        'name'     => $operator . '_date_from',
                        'label'    => Mage::helper('sendit_bliskapaczka')->__('Date from'),
                        //'after_element_html' => '<small>Comments</small>',
                        'tabindex' => 1,
                        'image'    => $this->getSkinUrl('images/grid-cal.gif'),
                        'format'   => Mage::app()->getLocale()->getDateTimeFormat(
                            Mage_Core_Model_Locale::FORMAT_TYPE_SHORT
                        ),
                        'time'     => true,
                        'value'    => date(
                            Mage::app()->getLocale()->getDateStrFormat(
                                Mage_Core_Model_Locale::FORMAT_TYPE_SHORT
                            ) . ' ' . Mage::app()->getLocale()->getTimeStrFormat(
                                Mage_Core_Model_Locale::FORMAT_TYPE_SHORT
                            ),
                            time()
                        ),
                    )
                );

                $fieldset->addField(
                    $operator . '_date_to',
                    'datetime',
                    array(
                        'name'     => $operator . '_date_to',
                        'label'    => Mage::helper('sendit_bliskapaczka')->__('Date to'),
                        //'after_element_html' => '<small>Comments</small>',
                        'tabindex' => 1,
                        'image'    => $this->getSkinUrl('images/grid-cal.gif'),
                        'format'   => Mage::app()->getLocale()->getDateTimeFormat(
                            Mage_Core_Model_Locale::FORMAT_TYPE_SHORT
                        ),
                        'time'     => true,
                        'value'    => date(
                            Mage::app()->getLocale()->getDateStrFormat(
                                Mage_Core_Model_Locale::FORMAT_TYPE_SHORT
                            ) . ' ' . Mage::app()->getLocale()->getTimeStrFormat(
                                Mage_Core_Model_Locale::FORMAT_TYPE_SHORT
                            ),
                            time()
                        ),
                    )
                );
            }
        } else {
            $fieldset = $form->addFieldset(
                'operators',
                [
                    'legend' => $this->__("Can't find operators"),
                    'class'  => 'fieldset-wide',
                ]
            );
        }
    }

    /**
     * Get order operators
     *
     * @return array
     */
    protected function getOperators()
    {
        $orderIds = [];
        $operators = [];

        $bliskaOrderCollection = Mage::getModel('sendit_bliskapaczka/order')->getCollection();

        $bliskaOrderCollection->addFieldToSelect('order_id');
        $bliskaOrderCollection->addFieldToFilter(
            'status',
            array('eq' => Sendit_Bliskapaczka_Model_Order::READY_TO_SEND)
        );

        foreach ($bliskaOrderCollection as $bliskaOrder) {
            $orderIds[] = $bliskaOrder->getOrderId();
        }

        $orderCollection = Mage::getModel('sales/order')->getCollection();
        $orderCollection->addFieldToSelect('*');
        $orderCollection->addFieldToFilter('entity_id', array('in' => $orderIds));

        foreach ($orderCollection as $order) {
            $shippingAddress = $order->getShippingAddress();

            if ($shippingAddress && $shippingAddress->getId()) {
                $operators[$shippingAddress->getPosOperator()] = $shippingAddress->getPosOperator();
            }
        }

        return $operators;
    }
}
