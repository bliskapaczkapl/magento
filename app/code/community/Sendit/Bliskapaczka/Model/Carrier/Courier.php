<?php

use Bliskapaczka\ApiClient;

/**
 * Bliskapaczka Courier shipping method
 *
 * @author Mateusz Koszutowski (mkoszutowski@divante.pl)
 */
class Sendit_Bliskapaczka_Model_Carrier_Courier
    extends Sendit_Bliskapaczka_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    const SHIPPING_CODE = 'sendit_bliskapaczka_courier';

    /**
     * Carrier's code
     *
     * @var string
     */
    protected $_code = self::SHIPPING_CODE;
}
