<?php

namespace Bliskapaczka\ApiClient\Bliskapaczka;

use Bliskapaczka\ApiClient\Bliskapaczka;
use Bliskapaczka\ApiClient\Mappers\Order as MappersOrder;

/**
 * Bliskapaczka class
 *
 * @author  Mateusz Koszutowski (mkoszutowski@divante.pl)
 * @version 0.1.0
 */
class Order extends Bliskapaczka
{
    const REQUEST_URL = 'order';

    /**
     * Call API method create order
     *
     * @param array $data
     */
    public function create(array $data)
    {
        $order = MappersOrder::createFromArray($data);
        $order->validate();

        $response = $this->doCall($this->getUrl(), json_encode($data), array(), 'POST');

        return $response;
    }
}
