<?php

class Sendit_Bliskapaczka_Adminhtml_OrderController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        return $this->loadLayout()->_setActiveMenu('sales/sales');
    }

    public function indexAction()
    {
        $this->_initAction();
        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout()->renderLayout();
    }

    public function listAction()
    {
        $this->loadLayout()->renderLayout();
    }

    public function exportCsvAction()
    {
        $fileName = 'order_list.csv';
        $content = $this->getLayout()->createBlock('sendit_bliskapaczka/adminhtml_order_grid');

        $this->_prepareDownloadResponse($fileName, $content->getCsvFile());
    }

    public function exportXmlAction()
    {
        $fileName = 'order_list.xml';
        $content = $this->getLayout()->createBlock('sendit_bliskapaczka/adminhtml_order_grid');

        $this->_prepareDownloadResponse($fileName, $content->getXml());
    }
}
