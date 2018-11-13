<?php

// @codingStandardsIgnoreStart
require_once 'Sendit/Bliskapaczka/controllers/Adminhtml/OrderController.php';
// @codingStandardsIgnoreEnd

/**
 * Class Sendit_Bliskapaczka_Adminhtml_AdviceController
 */
class Sendit_Bliskapaczka_Adminhtml_AdviceController extends Sendit_Bliskapaczka_Adminhtml_OrderController
{

    /**
     * Advice action
     */
    public function adviceAction()
    {
        if ($bliskaOrder = $this->_initBliskaOrder()) {
            try {
                $bliskaOrder->advice();

                $this->_getSession()->addSuccess(
                    $this->__('The order has been advised.')
                );
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('The order has not been advised.') . ' ' . $e->getMessage());
                Mage::log($e->getMessage(), null, Sendit_Bliskapaczka_Helper_Data::LOG_FILE);
            }

            $this->_redirect('*/order/view', array(self::BLISKA_ORDER_ID_PARAMETER => $bliskaOrder->getId()));
        }
    }

    /**
     * Mass advice action
     */
    public function massadviceAction()
    {
        $bliskaOrderIds = $this->getRequest()->getParam('entity_id');
        if (!is_array($bliskaOrderIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select order(s).'));
        } else {
            try {
                foreach ($bliskaOrderIds as $bliskaOrderId) {
                    Mage::helper('sendit_bliskapaczka')->advice($bliskaOrderId);
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) were advised.', count($bliskaOrderIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/order/index');
    }

    /**
     * Retry action
     */
    public function retryAction()
    {
        if ($bliskaOrder = $this->_initBliskaOrder()) {
            try {
                $bliskaOrder->retry();

                $this->_getSession()->addSuccess(
                    $this->__('The order has been updated.')
                );
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('The order has not been updated.') . ' ' . $e->getMessage());
                Mage::log($e->getMessage(), null, Sendit_Bliskapaczka_Helper_Data::LOG_FILE);
            }

            $this->_redirect('*/order/view', array(self::BLISKA_ORDER_ID_PARAMETER => $bliskaOrder->getId()));
        }
    }
}
