<?php

/**
 * Class Sendit_Bliskapaczka_Block_Adminhtml_Order_Grid
 */
class Sendit_Bliskapaczka_Block_Adminhtml_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Init grid
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('orderGrid');
        $this->setDefaultSort('order_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * Prepare grid collection object
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        /** @var Sendit_Bliskapaczka_Model_Resource_Order_Collection $collection */
        $collection = Mage::getResourceModel('sendit_bliskapaczka/order_collection');
        $collection->addFieldToSelect(
            array(
                'entity_id',
                'order_id',
                'number',
                'status',
                'delivery_type',
                'creation_date',
                'pos_operator',
                'pos_code',
                'pos_code_description',
                'advice_date',
                'tracking_number'
            )
        );

        $collection->getSelect()->joinLeft(
            'sales_flat_order',
            'main_table.order_id = sales_flat_order.entity_id',
            array('increment_id' => 'increment_id')
        );

        $collection->getSelect()->joinLeft(
            'sales_flat_order_address',
            'sales_flat_order.entity_id = sales_flat_order_address.parent_id AND
            sales_flat_order_address.address_type ="shipping"',
            array('firstname' => 'firstname', 'lastname' => 'lastname')
        );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Preparing grid columns
     *
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', [
            'header'    => $this->__('ID'),
            'type'      => 'number',
            'align'     => 'right',
            'index'     => 'entity_id',
            'filter_index' => 'main_table.entity_id',
        ]);

        $this->addColumn('increment_id', [
            'header'    => $this->__('order id'),
            'type'      => 'number',
            'align'     => 'right',
            'index'     => 'increment_id',
        ]);

        $this->_prepareFirstAndLastNameColumns();

        $this->addColumn('number', [
            'header'    => $this->__('number'),
            'type'      => 'text',
            'align'     => 'right',
            'index'     => 'number',
        ]);

        $this->addColumn(
            'status',
            [
                'header' => $this->__('status'),
                'align' => 'left',
                'index' => 'status',
                'type' => 'text',
                'filter_index' => 'main_table.status',
            ]
        );

        $this->addColumn(
            'delivery_type',
            [
                'header' => $this->__('delivery_type'),
                'align' => 'left',
                'index' => 'delivery_type',
                'type' => 'text',
            ]
        );

        $this->addColumn('creation_date', [
            'header'    =>  $this->__('creation_date'),
            'align'     =>  'left',
            'index'     =>  'creation_date',
            'type'      =>  'datetime',
        ]);

        $this->addColumn('pos_operator', [
            'header'    =>  $this->__('Operator Name'),
            'align'     =>  'left',
            'index'     =>  'pos_operator',
            'type'      =>  'text',
        ]);

        $this->addColumn('pos_code', [
            'header'    =>  $this->__('Destination Code'),
            'align'     =>  'left',
            'index'     =>  'pos_code',
            'type'      =>  'text',
        ]);

        $this->addColumn('pos_code_description', [
            'header'    =>  $this->__('Destination Point Description'),
            'align'     =>  'left',
            'index'     =>  'pos_code_description',
            'type'      =>  'text',
        ]);

        $this->addColumn('advice_date', [
            'header'    =>  $this->__('advice_date'),
            'align'     =>  'left',
            'index'     =>  'advice_date',
            'type'      =>  'datetime',
        ]);

        $this->addColumn('tracking_number', [
            'header'    => $this->__('tracking_number'),
            'type'      => 'text',
            'align'     => 'right',
            'index'     => 'tracking_number',
        ]);

        $this->addExportType('*/*/exportCsv', Mage::helper('core')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('core')->__('XML'));
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('entity_id');

        $this->getMassactionBlock()->addItem('get raport', array(
            'label'=> Mage::helper('sendit_bliskapaczka')->__('Get Report'),
            'url'  => $this->getUrl('*/*/report', array('' => ''))
        ));

        $this->getMassactionBlock()->addItem('mass cancel', array(
            'label'=> Mage::helper('sendit_bliskapaczka')->__('Cancel'),
            'url'  => $this->getUrl('*/*/masscancel', array('' => ''))
        ));

        $this->getMassactionBlock()->addItem('mass advice', array(
            'label'=> Mage::helper('sendit_bliskapaczka')->__('Advice'),
            'url'  => $this->getUrl('*/advice/massadvice', array('' => ''))
        ));

        return $this;
    }

    /**
     * Return row url for js event handlers
     *
     * @param Sendit_Bliskapaczka_Model_Order $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            '*/*/view',
            array(Sendit_Bliskapaczka_Adminhtml_OrderController::BLISKA_ORDER_ID_PARAMETER => $row->getId())
        );
    }

    /**
     * Grid url getter
     *
     * @deprecated after 1.3.2.3 Use getAbsoluteGridUrl() method instead
     *
     * @return string current grid url
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    /**
     * @throws Exception
     */
    protected function _prepareFirstAndLastNameColumns()
    {
        $this->addColumn('firstname', [
            'header'    => $this->__('First Name'),
            'type'      => 'text',
            'align'     => 'right',
            'index'     => 'firstname'
        ]);

        $this->addColumn('lastname', [
            'header'    => $this->__('Last Name'),
            'type'      => 'text',
            'align'     => 'right',
            'index'     => 'lastname'
        ]);
    }
}
