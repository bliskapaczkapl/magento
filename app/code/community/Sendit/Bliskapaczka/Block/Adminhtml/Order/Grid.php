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
        $collection->addFieldToSelect(['entity_id', 'order_id', 'number', 'status', 'delivery_type', 'creation_date', 'advice_date', 'tracking_number']);

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
        ]);

        $this->addColumn('order_id', [
            'header'    => $this->__('ID'),
            'type'      => 'number',
            'align'     => 'right',
            'index'     => 'order_id',
        ]);

        $this->addColumn('number', [
            'header'    => $this->__('number'),
            'type'      => 'number',
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

        $this->addColumn('advice_date', [
            'header'    =>  $this->__('advice_date'),
            'align'     =>  'left',
            'index'     =>  'advice_date',
            'type'      =>  'datetime',
        ]);

        $this->addColumn('tracking_number', [
            'header'    => $this->__('tracking_number'),
            'type'      => 'number',
            'align'     => 'right',
            'index'     => 'tracking_number',
        ]);

        $this->addExportType('*/*/exportCsv', Mage::helper('core')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('core')->__('XML'));
    }

    /**
     * Return row url for js event handlers
     *
     * @param Sendit_Bliskapaczka_Model_Order $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/list', array('order_id' => $row->getId()));
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
}
