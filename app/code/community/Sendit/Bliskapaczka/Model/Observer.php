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

        if (
            $order->getShippingMethod(true)->getMethod() != 'bliskapaczka_sendit_bliskapaczka'
            && $order->getShippingMethod(true)->getMethod() != 'bliskapaczka_courier_sendit_bliskapaczka_courier'
        ) {
            return $this;
        }

        /* @var $senditHelper Sendit_Bliskapaczka_Helper_Data */
        $senditHelper = Mage::helper('sendit_bliskapaczka');

        if ($order->getShippingMethod(true)->getMethod() == 'bliskapaczka_sendit_bliskapaczka') {
            /* @var Sendit_Bliskapaczka_Helper_Data $mapper */
            $mapper = Mage::getModel('sendit_bliskapaczka/mapper_order');
            $data = $mapper->getData($order, $senditHelper);

            /* @var $apiClient \Bliskapaczka\ApiClient\Bliskapaczka\Order */
            $apiClient = $senditHelper->getApiClientOrderAdvice();
        }

        if ($order->getShippingMethod(true)->getMethod() == 'bliskapaczka_courier_sendit_bliskapaczka_courier') {
            /* @var Sendit_Bliskapaczka_Helper_Data $mapper */
            $mapper = Mage::getModel('sendit_bliskapaczka/mapper_todoor');
            $data = $mapper->getData($order, $senditHelper);

            /* @var $apiClient \Bliskapaczka\ApiClient\Bliskapaczka */
            $apiClient = $senditHelper->getApiClientTodoorAdvice();
        }

        try {
            $response = $apiClient->create($data);
            $this->_saveResponse($order, $response);
        } catch (Exception $e) {
            Mage::throwException($senditHelper->__($e->getMessage()));
        }
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param json $response
     * @throws Exception
     */
    protected function _saveResponse($order, $response)
    {
        /** @var $coreHelper Mage_Core_Helper_Data */
        $coreHelper = Mage::helper('core');

        $decodedResponse = json_decode($response);

        //checking reposponce
        if ($response && $decodedResponse instanceof stdClass && empty($decodedResponse->errors)) {

            $bliskaOrder = Mage::getModel('sendit_bliskapaczka/order');
            $bliskaOrder->setOrderId($order->getId());
            $bliskaOrder->setNumber($coreHelper->stripTags($decodedResponse->number));
            $bliskaOrder->setStatus($coreHelper->stripTags($decodedResponse->status));
            $bliskaOrder->setDeliveryType($coreHelper->stripTags($decodedResponse->deliveryType));
            $bliskaOrder->setCreationDate($coreHelper->stripTags($decodedResponse->creationDate));
            $bliskaOrder->setAdviceDate($coreHelper->stripTags($decodedResponse->adviceDate));
            $bliskaOrder->setTrackingNumber($coreHelper->stripTags($decodedResponse->trackingNumber));

            $bliskaOrder->save();
        } else {
            //wyrzucamy wyjatek
            throw new Exception(Mage::helper('sendit_bliskapaczka')->__('Bliskapaczka: Error or empty API response'));
        }
    }

    /**
     * Validate module configuration data
     */
    public function validateAdminConfiguration()
    {
        $post = Mage::app()->getRequest()->getPost();

        $senditBliskapaczkaConfigData = $post['groups']['sendit_bliskapaczka'];
        $senditCourierConfigData = $post['groups']['sendit_bliskapaczka_courier'];

        /* @var $senditHelper Sendit_Bliskapaczka_Helper_Data */
        $senditHelper = Mage::helper('sendit_bliskapaczka');
        $sender = new \Bliskapaczka\ApiClient\Validator\Order\Advice\Sender();

        if ($senditBliskapaczkaConfigData['fields']['active']['value'] == '1') {
            /* @var Sendit_Bliskapaczka_Helper_Data $mapper */
            $mapper = Mage::getModel('sendit_bliskapaczka/mapper_admin');
            $data = $mapper->getData($senditBliskapaczkaConfigData, $senditHelper);

            $sender->setData($data);
            $sender->validate();
        }

        if ($senditCourierConfigData['fields']['active']['value'] == '1') {
            /* @var Sendit_Bliskapaczka_Helper_Data $mapper */
            $mapper = Mage::getModel('sendit_bliskapaczka/mapper_admin');
            $data = $mapper->getData($senditCourierConfigData, $senditHelper);

            $sender->setData($data);
            $sender->validate();
        }
    }

    /**
     * Update bliskapaczka shipping statuses
     *
     * Order statuses managed by bliskapaczka.pl are updating fast in processing order process.
     */
    public function updateFastStatuses()
    {
        spl_autoload_register(array($this, 'load'), true, true);

        $fastStatuses  = Sendit_Bliskapaczka_Helper_Data::FAST_STATUSES;

        $bliskaOrderCollection = Mage::getModel('sendit_bliskapaczka/order')->getCollection();
        $bliskaOrderCollection->addFieldToSelect('*');
        $bliskaOrderCollection->addFieldToFilter('status', array('in' => $fastStatuses));

        foreach ($bliskaOrderCollection as $bliskaOrder) {
            $bliskaOrder->get();
        }
    }

    /**
     * Update bliskapaczka shipping statuses
     *
     * Order statuses managed by provider and syhronized by bliskapaczka
     * are updating slow (once per 1.5h) in processing order process.
     */
    public function updateSlowStatuses()
    {
        spl_autoload_register(array($this, 'load'), true, true);

        $slowStatuses  = Sendit_Bliskapaczka_Helper_Data::SLOW_STATUSES;

        $bliskaOrderCollection = Mage::getModel('sendit_bliskapaczka/order')->getCollection();
        $bliskaOrderCollection->addFieldToSelect('*');
        $bliskaOrderCollection->addFieldToFilter('status', array('in' => $slowStatuses));

        foreach ($bliskaOrderCollection as $bliskaOrder) {
            $bliskaOrder->get();
        }
    }
}
