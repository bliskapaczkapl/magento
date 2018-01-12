<?php

use Bliskapaczka\ApiClient;

/**
 * Set of all method called by observers
 */
class Sendit_Bliskapaczka_Model_Observer
{

    /**
     * This an observer function for the event 'controller_front_init_before'.
     * It prepends our autoloader, so we can load the extra libraries.
     *
     * @param Varien_Event_Observer $event
     */
    public function controllerFrontInitBefore(Varien_Event_Observer $event)
    {
        unset($event);

        spl_autoload_register(array($this, 'load'), true, true);
    }

    /**
     * This function can autoloads classes starting with:
     * - Bliskapaczka/ApiClient
     *
     * @param string $class
     */
    public static function load($class)
    {
        if (preg_match('#^(Bliskapaczka\\\\ApiClient)\b#', $class)) {
            $libDir = Mage::getModuleDir('', 'Sendit_Bliskapaczka')
                . '/vendor/bliskapaczkapl/bliskapaczka-api-client/src/';
            $phpFile = $libDir . str_replace('\\', '/', $class) . '.php';

            // @codingStandardsIgnoreStart
            require_once($phpFile);
            // @codingStandardsIgnoreEnd
        }
    }

    /**
     * Set POS data
     *
     * @param Varien_Event_Observer $observer
     */
    public function setPosData(Varien_Event_Observer $observer)
    {
        $data = $observer->getEvent()->getRequest()->getParam('bliskapaczka');
        $quote = $observer->getEvent()->getQuote();
        $shippingAddress = $quote->getShippingAddress();

        $shippingAddress->setPosCode($data['posCode']);
        $shippingAddress->setPosOperator($data['posOperator']);

        $shippingAddress->setShippingDescription($shippingAddress->getShippingDescription() . ' ' . $data['posCode']);

        $shippingAddress->setCollectShippingRates(true)->collectShippingRates();
        $shippingAddress->save();

        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();
        $quote->save();
    }

    /**
     * Create new order in Bliska Paczka if shipping method is bliskapaczka
     *
     * @param Varien_Event_Observer $observer
     */
    public function createOrderViaApi(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        if (!$order) {
            return $this;
        }

        if ($order->getShippingMethod(true)->getMethod() != 'bliskapaczka_sendit_bliskapaczka') {
            return $this;
        }

        /* @var $senditHelper Sendit_Bliskapaczka_Helper_Data */
        $senditHelper = new Sendit_Bliskapaczka_Helper_Data();

        /* @var Sendit_Bliskapaczka_Helper_Data $mapper */
        $mapper = Mage::getModel('sendit_bliskapaczka/mapper_order');
        $data = $mapper->getData($order, $senditHelper);

        try {
            /* @var $apiClient \Bliskapaczka\ApiClient\Bliskapaczka */
            $apiClient = $senditHelper->getApiClient();
            $apiClient->createOrder($data);
        } catch (Exception $e) {
            Mage::throwException($senditHelper->__($e->getMessage()), 1);
        }
    }
}
