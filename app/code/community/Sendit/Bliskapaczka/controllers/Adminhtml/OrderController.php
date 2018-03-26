<?php

use Neodynamic\SDK\Web\WebClientPrint;
use Neodynamic\SDK\Web\DefaultPrinter;
use Neodynamic\SDK\Web\InstalledPrinter;
use Neodynamic\SDK\Web\PrintFile;
use Neodynamic\SDK\Web\PrintFilePDF;
use Neodynamic\SDK\Web\ClientPrintJob;

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
                $this->_getSession()->addError(
                    $this->__(
                        'You don\'t have permissions to manage this order because of one or more products are not permitted for your website.'
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
                Mage::logException($e);
            }
            $this->_redirect('*/*/view', array(self::BLISKA_ORDER_ID_PARAMETER => $bliskaOrder->getId()));
        }
    }

    /**
     * Report action
     */
    public function reportAction()
    {
        list($date, $operator) = $this->prepareData();

        $senditHelper = Mage::helper('sendit_bliskapaczka');
        /* @var $apiClient \Bliskapaczka\ApiClient\Bliskapaczka\Report */
        $apiClient = $senditHelper->getApiClientReport();
        if (isset($operator)) {$apiClient->setOperator($operator);
        }
        $apiClient->setStartPeriod($date);

        try {
            $content = $apiClient->get();
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError(
                $this->__('The report file has not been downloaded.') . ' ' . $e->getMessage()
            );
            Mage::logException($e);
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
     * @return array
     */
    protected function prepareData()
    {
        $date      = time();
        $entityIds = $this->getRequest()->getParam('entity_id');

        $bliskaOrderCollection = Mage::getModel('sendit_bliskapaczka/order')->getCollection();

        if ($entityIds) {
            $bliskaOrderCollection->addFieldToSelect('*');
            $bliskaOrderCollection->addFieldToFilter('entity_id', array('in' => $entityIds));
        }

        foreach ($bliskaOrderCollection as $bliskaOrder) {
            if (date($bliskaOrder->getCreationDate()) < $date) {
                $date = $bliskaOrder->getCreationDate();
            }
        }

        if ($bliskaOrderCollection) {
            $bliskaOrder = $bliskaOrderCollection->setPageSize(1, 1)->getLastItem();
            $order       = Mage::getModel('sales/order')->load($bliskaOrder->getOrderId());
            if ($order && $order->getId()) {
                $operator = $order->getShippingAddress()->getPosOperator();
            }
        }

        return array(
            $date,
            $operator,
        );
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
                $this->_getSession()->addError($this->__('The order has not been cancelled.') . ' ' . $e->getMessage());
                Mage::logException($e);
            }

            if ($url) {
                $content = Mage::helper('sendit_bliskapaczka')->downloadContent($url);

                $this->getResponse()->setHeader('Content-type', 'application/pdf');
                $this->getResponse()->setBody($content);
            } else {
                $this->_redirect('*/*/view', array(self::BLISKA_ORDER_ID_PARAMETER => $bliskaOrder->getId()));
            }
        }
    }

    /**
     * Waybill print action
     */
    public function waybillprintAction()
    {
        // @codingStandardsIgnoreStart
        include 'lib/Neodinamic/SDK/Web/WebClientPrint.php';
        // @codingStandardsIgnoreEnd

        //Set wcpcache folder RELATIVE to WebClientPrint.php file
        //FILE WRITE permission on this folder is required!!!
        WebClientPrint::$wcpCacheFolder = getcwd() . '/wcpcache/';

        $io = new Varien_Io_File();

        if ($io->fileExists(WebClientPrint::$wcpCacheFolder) == false) {
            //create wcpcache folder
            $old_umask = umask(0);
            $io->mkdir(WebClientPrint::$wcpCacheFolder, 0777);
            umask($old_umask);
        }

        // Clean built-in Cache
        // NOTE: Remove it if you implement your own cache system
        WebClientPrint::cacheClean(30); //in minutes

        $this->_title($this->__('Bliskapaczka'))->_title($this->__('Orders'));

        $order = $this->_initOrder();
        if ($order) {

            $isActionsNotPermitted = $order->getActionFlag(
                Mage_Sales_Model_Order::ACTION_FLAG_PRODUCTS_PERMISSION_DENIED
            );
            if ($isActionsNotPermitted) {
                $this->_getSession()->addError(
                    $this->__(
                        'You don\'t have permissions to manage this order because of one or more products are not permitted for your website.'
                    )
                );
            }

            $this->_initAction();

            $this->_title(sprintf("#%s", $order->getRealOrderId()));

            $this->renderLayout();
        }
    }

    /**
     * Neodinamic print
     */
    public function neodinamicprintAction()
    {
        // @codingStandardsIgnoreStart
        include 'lib/Neodinamic/SDK/Web/WebClientPrint.php';
        // @codingStandardsIgnoreEnd

        // Setting WebClientPrint
        WebClientPrint::$licenseOwner = Mage::getStoreConfig(
            Sendit_Bliskapaczka_Model_Carrier_Bliskapaczka::NEODYNAMIC_LICENSE_OWNER
        );
        WebClientPrint::$licenseKey   = Mage::getStoreConfig(
            Sendit_Bliskapaczka_Model_Carrier_Bliskapaczka::NEODYNAMIC_LICENSE_KEY
        );

        //Set wcpcache folder RELATIVE to WebClientPrint.php file
        //FILE WRITE permission on this folder is required!!!
        WebClientPrint::$wcpCacheFolder = 'wcpcache/';

        $url = '';

        // Process request
        // Generate ClientPrintJob? only if clientPrint param is in the query string
        $clientPrint       = $this->getRequest()->getParam(WebClientPrint::CLIENT_PRINT_JOB);
        $useDefaultPrinter = $this->getRequest()->getParam('useDefaultPrinter');
        $printerName       = $this->getRequest()->getParam('printerName');

        if (isset($clientPrint) && isset($useDefaultPrinter) && isset($printerName)) {

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
                    Mage::logException($e);
                }
            }

            if ($url) {
                $this->neodynamicPrint($url);
            }
        }
    }

    /**
     * Neodynamic print
     *
     * @param string $url
     */
    protected function neodynamicPrint($url)
    {
        //create a temp file name for our PDF file...
        $fileName = uniqid();

        $path = Mage::getBaseDir('media') . DS . 'tmp' . DS . 'pdf';

        $content = Mage::helper('sendit_bliskapaczka')->downloadContent($url);

        Mage::helper('sendit_bliskapaczka')->writeFile($path, $fileName, $content);

        $filePath = $path . DS . $fileName;

        //Create a ClientPrintJob obj that will be processed at the client side by the WCPP
        $cpj = new ClientPrintJob();
        //Create a PrintFilePDF object with the PDF file
        $cpj->printFile     = new PrintFilePDF($filePath, $fileName, null);
        $cpj->clientPrinter = new DefaultPrinter();

        //Send ClientPrintJob back to the client
        ob_start();
        ob_clean();
        $this->getResponse()->setHeader('Content-type', 'application/octet-stream');
        $this->getResponse()->setBody($cpj->sendToClient());
        ob_end_flush();
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
                Mage::logException($e);
            }

            $this->_redirect('*/*/view', array(self::BLISKA_ORDER_ID_PARAMETER => $bliskaOrder->getId()));
        }
    }

    /**
     * Initialize order model instance
     *
     * @return Mage_Sales_Model_Order || false
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

        return $order;
    }

    /**
     * Initialize order model instance
     *
     * @return Sendit_Bliskapaczka_Model_Order || false
     */
    protected function _initBliskaOrder()
    {
        $id = $this->getRequest()->getParam(self::BLISKA_ORDER_ID_PARAMETER);

        $bliskaOrder = Mage::getModel('sendit_bliskapaczka/order')->load($id);

        if (!$bliskaOrder || !$bliskaOrder->getId()) {
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
