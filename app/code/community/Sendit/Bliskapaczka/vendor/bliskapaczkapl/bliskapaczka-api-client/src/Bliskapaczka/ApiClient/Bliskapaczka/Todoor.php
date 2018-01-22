<?php

namespace Bliskapaczka\ApiClient\Bliskapaczka;

use Bliskapaczka\ApiClient\Bliskapaczka;
use Bliskapaczka\ApiClient\Mappers\Todoor as MappersTodoor;

/**
 * Bliskapaczka class
 *
 * @author  Mateusz Koszutowski (mkoszutowski@divante.pl)
 * @version 0.1.0
 */
class Todoor extends Bliskapaczka
{
    const REQUEST_URL = 'order/todoor';

    /**
     * Call API method create order
     *
     * @param array $data
     */
    public function create(array $data)
    {
        $order = MappersTodoor::createFromArray($data);
        $order->validate();

        $response = $this->doCall($this->getUrl(), json_encode($data), array(), 'POST');

        return $response;
    }
}
