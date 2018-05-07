<?php

/**
 * Class Sendit_Bliskapaczka_Adminhtml_ReportController
 */
class Sendit_Bliskapaczka_Adminhtml_ReportController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        /** @var Mage_Admin_Model_Session $session */
        $session = Mage::getSingleton('admin/session');

        return $session->isAllowed('sendit_bliskapaczka/report');
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
     * Save action
     */
    public function generateAction()
    {
        /** @var $coreHelper Mage_Core_Helper_Data */
        $coreHelper = Mage::helper('core');

        $operator = $this->getRequest()->getParam('operator');
        $dateFrom = $this->getRequest()->getParam($operator . '_date_from');

        $operator = $this->getRequest()->getParam('operator');
        $dateTo = $this->getRequest()->getParam($operator . '_date_to');

        $senditHelper = Mage::helper('sendit_bliskapaczka');
        /* @var $apiClient \Bliskapaczka\ApiClient\Bliskapaczka\Report */
        $apiClient = $senditHelper->getApiClientReport();
        $apiClient->setOperator($coreHelper->stripTags($operator));
        if($dateFrom) {
            $apiClient->setStartPeriod($coreHelper->stripTags($dateFrom));
        }
        if($dateTo) {
            $apiClient->setEndPeriod($coreHelper->stripTags($dateTo));
        }

        try {
            $content = $apiClient->get();
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError(
                Mage::helper('sendit_bliskapaczka')->__('The report file has not been downloaded.') .
                ' ' . $e->getMessage()
            );
            Mage::logException($e);
        }
        if (isset($content)) {
            $this->_getSession()->addSuccess(
                Mage::helper('sendit_bliskapaczka')->__('The report file has been downloaded.')
            );

            $this->getResponse()->setHeader('Content-type', 'application/pdf');
            $this->getResponse()->setBody($content);
        } else {
            $this->_getSession()->addError(
                Mage::helper('sendit_bliskapaczka')->__('The report file has not been downloaded.')
            );
            $this->_redirect('*/*/index');
        }
    }
}
