<?php
//Set custom memory limit
ini_set('memory_limit', '512M');
ini_set('error_reporting', E_ALL);

$GLOBALS['ROOT_DIR'] = dirname(__FILE__) . '/../..';
$GLOBALS['APP_DIR'] = $GLOBALS['ROOT_DIR'] . '/app';
$GLOBALS['VENDOR_DIR'] = $GLOBALS['APP_DIR'] . '/code/community/Sendit/Bliskapaczka/vendor';

require_once $GLOBALS['VENDOR_DIR'] . '/autoload.php';

$file = $GLOBALS['VENDOR_DIR'] . '/firegento/magento/app/Mage.php';
file_put_contents($file, str_replace('final class Mage', 'class Mage', file_get_contents($file)));

//Define include path for Magento and BliskaPaczka Module
ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . $GLOBALS['VENDOR_DIR'] . '/firegento/magento');
ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . $GLOBALS['VENDOR_DIR'] . '/firegento/magento/app');
// ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . $GLOBALS['APP_DIR']);

//Include Magento libraries
require_once 'Mage.php';
//Start the Magento application
Mage::app('default');
//Avoid issues "Headers already send"
session_start();
