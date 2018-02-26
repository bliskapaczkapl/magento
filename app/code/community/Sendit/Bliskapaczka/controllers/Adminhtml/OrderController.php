<?php

/**
 * Class Sendit_Bliskapaczka_Adminhtml_OrderController
 */
class Sendit_Bliskapaczka_Adminhtml_OrderController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        /** @var Mage_Admin_Model_Session $session */
        $session = Mage::getSingleton('admin/session');

        return $session->isAllowed('sendit_bliskapaczka/order');
    }

    /**
     * @return Mage_Adminhtml_Controller_Action
     */
    protected function _initAction()
    {
        return $this->loadLayout()->_setActiveMenu('sales/sales');
    }

    /**
     * Index action
     */
    public function indexAction()
    {
        $this->_initAction();
        $this->renderLayout();
    }

    /**
     * Grid action
     */
    public function gridAction()
    {
        $this->loadLayout()->renderLayout();
    }

    /**
     * List action
     */
    public function listAction()
    {
        $this->loadLayout()->renderLayout();
    }

    /**
     * ExportCsv action
     */
    public function exportCsvAction()
    {
        $fileName = 'order_list.csv';
        $content = $this->getLayout()->createBlock('sendit_bliskapaczka/adminhtml_order_grid');

        $this->_prepareDownloadResponse($fileName, $content->getCsvFile());
    }

    /**
     * ExportXml action
     */
    public function exportXmlAction()
    {
        $fileName = 'order_list.xml';
        $content = $this->getLayout()->createBlock('sendit_bliskapaczka/adminhtml_order_grid');

        $this->_prepareDownloadResponse($fileName, $content->getXml());
    }
}
