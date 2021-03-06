<?php

use Bliskapaczka\ApiClient;

/**
 * Bliskapaczka API helper
 *
 * @author Mateusz Koszutowski (mkoszutowski@divante.pl)
 */
class Sendit_Bliskapaczka_Helper_Api extends Mage_Core_Helper_Data
{

    /**
     * For choosen orders create string with order numbers to get data from API
     * @return string
     */
    public function prepareDataForMassActionReport()
    {
        $entityIds = $this->_getRequest()->getParam('entity_id');

        $bliskaOrderCollection = Mage::getModel('sendit_bliskapaczka/order')->getCollection();

        if ($entityIds) {
            $bliskaOrderCollection->addFieldToSelect('*');
            $bliskaOrderCollection->addFieldToFilter('entity_id', array('in' => $entityIds));
        }

        $numbers = '';
        foreach ($bliskaOrderCollection as $bliskaOrder) {
            if ($numbers && $bliskaOrder->getNumber()) {
                $numbers .= ',' . $bliskaOrder->getNumber();
            } else {
                $numbers = $bliskaOrder->getNumber();
            }
        }

        return $numbers;
    }

    /**
     * Get Bliskapaczka API Client
     *
     * @param Sendit_Bliskapaczka_Helper_Data $senditHelper
     * @return \Bliskapaczka\ApiClient\Bliskapaczka
     */
    public function getApiClientPos($senditHelper)
    {
        $apiClient = new \Bliskapaczka\ApiClient\Bliskapaczka\Pos(
            Mage::getStoreConfig($senditHelper::API_KEY_XML_PATH),
            $senditHelper->getApiMode(Mage::getStoreConfig($senditHelper::API_TEST_MODE_XML_PATH))
        );

        return $apiClient;
    }

    /**
     * Get Bliskapaczka API Client
     *
     * @param Sendit_Bliskapaczka_Helper_Data $senditHelper
     * @return \Bliskapaczka\ApiClient\Bliskapaczka
     */
    public function getApiClientOrder($senditHelper)
    {
        $apiClient = new \Bliskapaczka\ApiClient\Bliskapaczka\Order(
            Mage::getStoreConfig($senditHelper::API_KEY_XML_PATH),
            $senditHelper->getApiMode(Mage::getStoreConfig($senditHelper::API_TEST_MODE_XML_PATH))
        );

        return $apiClient;
    }

    /**
     * Get Bliskapaczka API Client
     *
     * @param Sendit_Bliskapaczka_Helper_Data $senditHelper
     * @return \Bliskapaczka\ApiClient\Bliskapaczka
     */
    public function getApiClientOrderAdvice($senditHelper)
    {
        $apiClient = new \Bliskapaczka\ApiClient\Bliskapaczka\Order\Advice(
            Mage::getStoreConfig($senditHelper::API_KEY_XML_PATH),
            $senditHelper->getApiMode(Mage::getStoreConfig($senditHelper::API_TEST_MODE_XML_PATH))
        );

        return $apiClient;
    }

    /**
     * Get Bliskapaczka API Client
     *
     * @param Sendit_Bliskapaczka_Helper_Data $senditHelper
     * @return \Bliskapaczka\ApiClient\Bliskapaczka
     */
    public function getApiClientOrderReceiverValidator($senditHelper)
    {
        $apiClient = new \Bliskapaczka\ApiClient\Bliskapaczka\Order\Receiver(
            Mage::getStoreConfig($senditHelper::API_KEY_XML_PATH),
            $senditHelper->getApiMode(Mage::getStoreConfig($senditHelper::API_TEST_MODE_XML_PATH))
        );

        return $apiClient;
    }

    /**
     * Get Bliskapaczka API Client
     *
     * @param Sendit_Bliskapaczka_Helper_Data $senditHelper
     * @return \Bliskapaczka\ApiClient\Bliskapaczka
     */
    public function getApiClientTodoor($senditHelper)
    {
        $apiClient = new \Bliskapaczka\ApiClient\Bliskapaczka\Todoor(
            Mage::getStoreConfig($senditHelper::API_KEY_XML_PATH),
            $senditHelper->getApiMode(Mage::getStoreConfig($senditHelper::API_TEST_MODE_XML_PATH))
        );

        return $apiClient;
    }

    /**
     * Get Bliskapaczka API Client
     *
     * @param Sendit_Bliskapaczka_Helper_Data $senditHelper
     * @return \Bliskapaczka\ApiClient\Bliskapaczka
     */
    public function getApiClientTodoorAdvice($senditHelper)
    {
        $apiClient = new \Bliskapaczka\ApiClient\Bliskapaczka\Todoor\Advice(
            Mage::getStoreConfig($senditHelper::API_KEY_XML_PATH),
            $senditHelper->getApiMode(Mage::getStoreConfig($senditHelper::API_TEST_MODE_XML_PATH))
        );

        return $apiClient;
    }

    /**
     * Get Bliskapaczka API Client
     *
     * @param Sendit_Bliskapaczka_Helper_Data $senditHelper
     * @return \Bliskapaczka\ApiClient\Bliskapaczka
     */
    public function getApiClientTodoorReceiverValidator($senditHelper)
    {
        $apiClient = new \Bliskapaczka\ApiClient\Bliskapaczka\Todoor\Receiver(
            Mage::getStoreConfig($senditHelper::API_KEY_XML_PATH),
            $senditHelper->getApiMode(Mage::getStoreConfig($senditHelper::API_TEST_MODE_XML_PATH))
        );

        return $apiClient;
    }

    /**
     * Get Bliskapaczka API Client
     *
     * @param string $shippingMethod
     * @param string $paymentMethod
     * @param Sendit_Bliskapaczka_Helper_Data $senditHelper
     * @param bool $advice
     * @param boot $receiverValidator
     * @param boot $enableAutoDdviceForPayPal
     * @return mixed
     */
    public function getApiClientForOrder(
        $shippingMethod,
        $paymentMethod,
        $senditHelper,
        $advice = false,
        $receiverValidator = false,
        $enableAutoDdviceForPayPal = false
    ) {
        if (!$advice) {
            $autoAdvice = Mage::getStoreConfig($senditHelper::API_AUTO_ADVICE_XML_PATH);
        }

        if (!$enableAutoDdviceForPayPal) {
            $enableAutoDdviceForPayPal = Mage::getStoreConfig($senditHelper::API_AUTO_ADVICE_XML_PATH);
        }

        $methodName = $this->getApiClientForOrderMethodName(
            $shippingMethod,
            $paymentMethod,
            $advice,
            $autoAdvice,
            $receiverValidator,
            $enableAutoDdviceForPayPal,
            $senditHelper
        );

        return $this->{$methodName}($senditHelper);
    }

    /**
     * Get method name to bliskapaczka api client create order action
     *
     * @param string $shippingMethod
     * @param string $paymentMethod
     * @param bool $advice
     * @param string $autoAdvice
     * @param boot $receiverValidator
     * @param boot $enableAutoDdviceForPayPal
     * @param Sendit_Bliskapaczka_Helper_Data $senditHelper
     * @return string
     */
    public function getApiClientForOrderMethodName(
        $shippingMethod,
        $paymentMethod,
        $advice,
        $autoAdvice,
        $receiverValidator,
        $enableAutoDdviceForPayPal,
        $senditHelper
    ) {
        $type = 'Todoor';

        if ($senditHelper->isPoint($shippingMethod)) {
            $type = 'Order';
        }

        $methodName = 'getApiClient' . $type;

        if ($advice ||
            ($autoAdvice && strpos($paymentMethod, 'paypal') === false) ||
            ($autoAdvice && is_int(strpos($paymentMethod, 'paypal')) && !$enableAutoDdviceForPayPal)
        ) {
            $methodName .= 'Advice';
        }

        if ($receiverValidator) {
            $methodName .= 'ReceiverValidator';
        }

        return $methodName;
    }

    /**
     * Get Bliskapaczka API Client
     *
     * @param Sendit_Bliskapaczka_Helper_Data $senditHelper
     * @return \Bliskapaczka\ApiClient\Bliskapaczka
     */
    public function getApiClientReport($senditHelper)
    {
        $apiClient = new \Bliskapaczka\ApiClient\Bliskapaczka\Report(
            Mage::getStoreConfig($senditHelper::API_KEY_XML_PATH),
            $senditHelper->getApiMode(Mage::getStoreConfig($senditHelper::API_TEST_MODE_XML_PATH))
        );

        return $apiClient;
    }

    /**
     * Get Bliskapaczka API Client
     *
     * @param Sendit_Bliskapaczka_Helper_Data $senditHelper
     * @return \Bliskapaczka\ApiClient\Bliskapaczka
     */
    public function getApiClientWaybill($senditHelper)
    {
        $apiClient = new \Bliskapaczka\ApiClient\Bliskapaczka\Order\Waybill(
            Mage::getStoreConfig($senditHelper::API_KEY_XML_PATH),
            $senditHelper->getApiMode(Mage::getStoreConfig($senditHelper::API_TEST_MODE_XML_PATH))
        );

        return $apiClient;
    }

    /**
     * Get Bliskapaczka API Client
     *
     * @param Sendit_Bliskapaczka_Helper_Data $senditHelper
     * @return \Bliskapaczka\ApiClient\Bliskapaczka
     */
    public function getApiClientConfirm($senditHelper)
    {
        $apiClient = new \Bliskapaczka\ApiClient\Bliskapaczka\Order\Confirm(
            Mage::getStoreConfig($senditHelper::API_KEY_XML_PATH),
            $senditHelper->getApiMode(Mage::getStoreConfig($senditHelper::API_TEST_MODE_XML_PATH))
        );

        return $apiClient;
    }

    /**
     * Get Bliskapaczka API Client
     *
     * @param Sendit_Bliskapaczka_Helper_Data $senditHelper
     * @return \Bliskapaczka\ApiClient\Bliskapaczka
     */
    public function getApiClientRetry($senditHelper)
    {
        $apiClient = new \Bliskapaczka\ApiClient\Bliskapaczka\Order\Retry(
            Mage::getStoreConfig($senditHelper::API_KEY_XML_PATH),
            $senditHelper->getApiMode(Mage::getStoreConfig($senditHelper::API_TEST_MODE_XML_PATH))
        );

        return $apiClient;
    }

    /**
     * Get Bliskapaczka API Client
     *
     * @param Sendit_Bliskapaczka_Helper_Data $senditHelper
     * @return \Bliskapaczka\ApiClient\Bliskapaczka
     */
    public function getApiClientCancel($senditHelper)
    {
        $apiClient = new \Bliskapaczka\ApiClient\Bliskapaczka\Order\Cancel(
            Mage::getStoreConfig($senditHelper::API_KEY_XML_PATH),
            $senditHelper->getApiMode(Mage::getStoreConfig($senditHelper::API_TEST_MODE_XML_PATH))
        );

        return $apiClient;
    }
}
