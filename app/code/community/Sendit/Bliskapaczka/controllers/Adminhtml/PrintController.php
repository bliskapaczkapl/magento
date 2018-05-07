<?php

use Neodynamic\SDK\Web\WebClientPrint;
use Neodynamic\SDK\Web\DefaultPrinter;
use Neodynamic\SDK\Web\InstalledPrinter;
use Neodynamic\SDK\Web\PrintFile;
use Neodynamic\SDK\Web\PrintFilePDF;
use Neodynamic\SDK\Web\ClientPrintJob;

// @codingStandardsIgnoreStart
require_once 'Sendit/Bliskapaczka/controllers/Adminhtml/OrderController.php';
// @codingStandardsIgnoreEnd

/**
 * Class Sendit_Bliskapaczka_Adminhtml_PrintController
 */
class Sendit_Bliskapaczka_Adminhtml_PrintController extends Sendit_Bliskapaczka_Adminhtml_OrderController
{
    /**
     * Waybill print action
     */
    public function waybillprintAction(){
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
                $cpj = Mage::helper('sendit_bliskapaczka/print')->neodynamicPrint($url);

                //Send ClientPrintJob back to the client
                ob_start();
                ob_clean();
                $this->getResponse()->setHeader('Content-type', 'application/octet-stream');
                $this->getResponse()->setBody($cpj->sendToClient());
                ob_end_flush();
            }
        }
    }
}
