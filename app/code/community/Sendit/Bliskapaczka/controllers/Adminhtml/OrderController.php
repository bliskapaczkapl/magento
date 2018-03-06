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
    public function viewAction()
    {
        $this->_title($this->__('Bliskapaczka'))->_title($this->__('Orders'));

        $order = $this->_initOrder();
        if ($order) {

            $isActionsNotPermitted = $order->getActionFlag(
                Mage_Sales_Model_Order::ACTION_FLAG_PRODUCTS_PERMISSION_DENIED
            );
            if ($isActionsNotPermitted) {
                $this->_getSession()->addError($this->__('You don\'t have permissions to manage this order because of one or more products are not permitted for your website.'));
            }

            $this->_initAction();

            $this->_title(sprintf("#%s", $order->getRealOrderId()));

            $this->renderLayout();
        }
    }

    /**
     * Cancel action
     */
    public function cancelAction()
    {
        if ($bliskaOrder = $this->_initBliskaOrder()) {
            try {
                $bliskaOrder->cancel()->save();

                $this->_getSession()->addSuccess(
                    $this->__('The order has been cancelled.')
                );
            }
            catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
            catch (Exception $e) {
                $this->_getSession()->addError($this->__('The order has not been cancelled.') . ' ' . $e->getMessage());
                Mage::logException($e);
            }
            $this->_redirect('*/*/view', array('bliska_order_id' => $bliskaOrder->getId()));
        }
    }

    /**
     * Waybill action
     */
    public function waybillAction()
    {
        $url = '';

        if ($bliskaOrder = $this->_initBliskaOrder()) {
            try {
                $url = $bliskaOrder->waybill();

                $this->_getSession()->addSuccess(
                    $this->__('The waybill has been downloaded.')
                );
            }
            catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
            catch (Exception $e) {
                $this->_getSession()->addError($this->__('The order has not been cancelled.') . ' ' . $e->getMessage());
                Mage::logException($e);
            }

            if($url) {

                $http = new Varien_Http_Adapter_Curl();
                $http->write('GET', $url);
                $content = $http->read();
                $http->close();

                $this->getResponse()->setHeader('Content-type', 'application/pdf');
                $this->getResponse()->setBody($content);
            } else {
                $this->_redirect('*/*/view', array('bliska_order_id' => $bliskaOrder->getId()));
            }
        }
    }

    /**
     * Initialize order model instance
     *
     * @return Mage_Sales_Model_Order || false
     */
    protected function _initOrder()
    {
        $id = $this->getRequest()->getParam('bliska_order_id');

        $bliskaOrder = Mage::getModel('sendit_bliskapaczka/order')->load($id);

        if(!$bliskaOrder || !$bliskaOrder->getId()) {
            $this->_getSession()->addError($this->__('This order no longer exists.'));
            $this->_redirect('*/*/');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }

        $order = Mage::getModel('sales/order')->load($bliskaOrder->getOrderId());

        if (!$order->getId()) {
            $this->_getSession()->addError($this->__('This order no longer exists.'));
            $this->_redirect('*/*/');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }

        Mage::register('bliska_order', $bliskaOrder);
        Mage::register('sales_order', $order);
        Mage::register('current_order', $order);

        return $order;
    }

    /**
     * Initialize order model instance
     *
     * @return Sendit_Bliskapaczka_Model_Order || false
     */
    protected function _initBliskaOrder()
    {
        $id = $this->getRequest()->getParam('bliska_order_id');

        $bliskaOrder = Mage::getModel('sendit_bliskapaczka/order')->load($id);

        if(!$bliskaOrder || !$bliskaOrder->getId()) {
            $this->_getSession()->addError($this->__('This order no longer exists.'));
            $this->_redirect('*/*/');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }

        return $bliskaOrder;
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
