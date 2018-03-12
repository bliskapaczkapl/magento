<?php

set_include_path(get_include_path() . PATH_SEPARATOR . realpath(__DIR__ . '/../../../'));
require_once '../../../app/Mage.php';
Mage::app('default');

spl_autoload_register(function ($class) {
    if (preg_match('#^(Bliskapaczka\\\\ApiClient)\b#', $class)) {
        $libDir = Mage::getModuleDir('', 'Sendit_Bliskapaczka')
            . '/vendor/bliskapaczkapl/bliskapaczka-api-client/src/';
        $phpFile = $libDir . str_replace('\\', '/', $class) . '.php';

        // @codingStandardsIgnoreStart
        require_once($phpFile);
        // @codingStandardsIgnoreEnd
    }
});


$time = microtime(true);
try {

    /** @var $coreHelper Mage_Core_Helper_Data */
    $coreHelper = Mage::helper('core');

    $order = Mage::getModel('sales/order')->load(220);

    if(!$order || !$order->getId()) {
        echo 'Cant find order';
        return;
    }

    /* @var $senditHelper Sendit_Bliskapaczka_Helper_Data */
    $senditHelper = new Sendit_Bliskapaczka_Helper_Data();

    if ($order->getShippingMethod(true)->getMethod() == 'bliskapaczka_sendit_bliskapaczka') {
        /* @var Sendit_Bliskapaczka_Helper_Data $mapper */
        $mapper = Mage::getModel('sendit_bliskapaczka/mapper_order');
        $data = $mapper->getData($order, $senditHelper);

        /* @var $apiClient \Bliskapaczka\ApiClient\Bliskapaczka */
        $apiClient = $senditHelper->getApiClientOrder();
    }

    if ($order->getShippingMethod(true)->getMethod() == 'bliskapaczka_courier_sendit_bliskapaczka_courier') {
        /* @var Sendit_Bliskapaczka_Helper_Data $mapper */
        $mapper = Mage::getModel('sendit_bliskapaczka/mapper_todoor');
        $data = $mapper->getData($order, $senditHelper);

        /* @var $apiClient \Bliskapaczka\ApiClient\Bliskapaczka */
        $apiClient = $senditHelper->getApiClientTodoor();
    }

    $response = $apiClient->create($data);


    $decodedResponse = json_decode($response);

    if($response && $decodedResponse instanceof stdClass && empty($decodedResponse->errors)) {

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
    }

    $response = json_decode($response);

    var_dump($response);

    echo "Done." . PHP_EOL;
} catch (Exception $e) {
    Mage::logException($e);
    print_r($e->getMessage());
    echo PHP_EOL . PHP_EOL;
    print_r($e->getTraceAsString());
}
echo "Finished in " . (microtime(true) - $time) . " seconds.'" . PHP_EOL;


function filterResponse(Array $decodedReposnse)
{
    if (!empty($decodedReposnse)) {
        foreach ($decodedReposnse as &$response) {
            $response = Mage::helper('core')->stripTags($response);
        }
    }

    return $decodedReposnse;
}