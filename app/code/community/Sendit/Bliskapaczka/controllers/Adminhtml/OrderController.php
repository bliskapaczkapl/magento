<?php

/**
 * Class Sendit_Bliskapaczka_Adminhtml_OrderController
 */
class Sendit_Bliskapaczka_Adminhtml_OrderController extends Mage_Adminhtml_Controller_Action
{

    const BLISKA_ORDER_ID_PARAMETER = 'bliskaOrderId';

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        /** @var Mage_Admin_Model_Session $session */
        $session = Mage::getSingleton('admin/session');

        return $session->isAllowed('sales/sendit_bliskapaczka/order');
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

        list($order, $bliskaOrder) = $this->_initOrder();
        if ($order) {
            $isActionsNotPermitted = $order->getActionFlag(
                Mage_Sales_Model_Order::ACTION_FLAG_PRODUCTS_PERMISSION_DENIED
            );
            if ($isActionsNotPermitted) {
                $this->_getSession()->addError(
                    $this->__(
                        'You don\'t have permissions to manage this order because of one ' .
                        'or more products are not permitted for your website.'
                    )
                );
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
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('The order has not been cancelled.') . ' ' . $e->getMessage());
                Mage::log($e->getMessage(), null, Sendit_Bliskapaczka_Helper_Data::LOG_FILE);
            }
            $this->_redirect('*/*/view', array(self::BLISKA_ORDER_ID_PARAMETER => $bliskaOrder->getId()));
        }
    }

    /**
     * Report action
     */
    public function reportAction()
    {
        $numbers = Mage::helper('sendit_bliskapaczka/api')->prepareDataForMassActionReport();

        /* @var $senditHelper Sendit_Bliskapaczka_Helper_Data */
        $senditHelper = Mage::helper('sendit_bliskapaczka');
        /* @var $senditApiHelper Sendit_Bliskapaczka_Helper_Api */
        $senditApiHelper = Mage::helper('sendit_bliskapaczka/api');
        /* @var $apiClient \Bliskapaczka\ApiClient\Bliskapaczka\Report */
        $apiClient = $senditApiHelper->getApiClientReport($senditHelper);
        $apiClient->setNumbers($numbers);

        try {
            $content = $apiClient->get();
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError(
                $this->__('The report file has not been downloaded.') . ' ' . $e->getMessage()
            );
            Mage::log($e->getMessage(), null, Sendit_Bliskapaczka_Helper_Data::LOG_FILE);
        }

        if ($content) {
            $this->_getSession()->addSuccess(
                $this->__('The report file has been downloaded.')
            );

            $this->getResponse()->setHeader('Content-type', 'application/pdf');
            $this->getResponse()->setBody($content);
        } else {
            $this->_getSession()->addError($this->__('The report file has not been downloaded.'));
            $this->_redirect('*/*/index');
        }
    }

    /**
     * Mass cancel action
     */
    public function masscancelAction()
    {
        $bliskaOrderIds = $this->getRequest()->getParam('entity_id');
        if (!is_array($bliskaOrderIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select order(s).'));
        } else {
            try {
                foreach ($bliskaOrderIds as $bliskaOrderId) {
                    Mage::helper('sendit_bliskapaczka')->cancel($bliskaOrderId);
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) were canceled.', count($bliskaOrderIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::log($e->getMessage(), null, Sendit_Bliskapaczka_Helper_Data::LOG_FILE);
            }
        }

        $this->_redirect('*/*/index');
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
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    $this->__('The order has not been downloaded.') . ' ' . $e->getMessage()
                );
                Mage::log($e->getMessage(), null, Sendit_Bliskapaczka_Helper_Data::LOG_FILE);
            }

            if ($url) {
                $content = Mage::helper('sendit_bliskapaczka/print')->downloadContent($url);

                $this->getResponse()->setHeader('Content-type', 'application/pdf');
                $this->getResponse()->setBody($content);
            } else {
                $this->_redirect('*/*/view', array(self::BLISKA_ORDER_ID_PARAMETER => $bliskaOrder->getId()));
            }
        }
    }

    /**
     * Waybill action
     */
    public function confirmAction()
    {
        try {
            /* @var $senditHelper Sendit_Bliskapaczka_Helper_Data */
            $senditHelper = Mage::helper('sendit_bliskapaczka');
            /* @var $senditApiHelper Sendit_Bliskapaczka_Helper_Api */
            $senditApiHelper = Mage::helper('sendit_bliskapaczka/api');
            /* @var $apiClient \Bliskapaczka\ApiClient\Bliskapaczka\Report */
            $apiClient = $senditApiHelper->getApiClientConfirm($senditHelper);
            $apiClient->setOperator('POCZTA');
            $apiClient->confirm();

            $this->_getSession()->addSuccess(
                $this->__('The confirmation has been sent.')
            );
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError(
                $this->__('The confirmation has not been sent.') . ' ' . $e->getMessage()
            );
            Mage::log($e->getMessage(), null, Sendit_Bliskapaczka_Helper_Data::LOG_FILE);
        }

        $this->_redirect('*/*/index');
    }

    /**
     * Get action
     */
    public function getAction()
    {
        if ($bliskaOrder = $this->_initBliskaOrder()) {
            try {
                $bliskaOrder->get();

                $this->_getSession()->addSuccess(
                    $this->__('The order has been updated.')
                );
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('The order has not been updated.') . ' ' . $e->getMessage());
                Mage::log($e->getMessage(), null, Sendit_Bliskapaczka_Helper_Data::LOG_FILE);
            }

            $this->_redirect('*/*/view', array(self::BLISKA_ORDER_ID_PARAMETER => $bliskaOrder->getId()));
        }
    }

    /**
     * Initialize order model instance
     *
     * @return Mage_Sales_Model_Order || Sendit_Bliskapaczka_Model_Order
     */
    protected function _initOrder()
    {
        $id = $this->getRequest()->getParam(self::BLISKA_ORDER_ID_PARAMETER);

        $bliskaOrder = Mage::getModel('sendit_bliskapaczka/order')->load($id);

        if (!$bliskaOrder || !$bliskaOrder->getId()) {
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

        return array($order, $bliskaOrder);
    }

    /**
     * Initialize order model instance
     *
     * @return Sendit_Bliskapaczka_Model_Order || false
     */
    protected function _initBliskaOrder()
    {
        list($order, $bliskaOrder) = $this->_initOrder();

        return $bliskaOrder;
    }

    /**
     * ExportCsv action
     */
    public function exportCsvAction()
    {
        $fileName = 'order_list.csv';
        $content  = $this->getLayout()->createBlock('sendit_bliskapaczka/adminhtml_order_grid');

        $this->_prepareDownloadResponse($fileName, $content->getCsvFile());
    }

    /**
     * ExportXml action
     */
    public function exportXmlAction()
    {
        $fileName = 'order_list.xml';
        $content  = $this->getLayout()->createBlock('sendit_bliskapaczka/adminhtml_order_grid');

        $this->_prepareDownloadResponse($fileName, $content->getXml());
    }
}
