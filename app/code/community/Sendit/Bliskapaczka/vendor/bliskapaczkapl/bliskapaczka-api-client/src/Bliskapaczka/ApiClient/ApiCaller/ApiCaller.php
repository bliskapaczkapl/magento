<?php

namespace Bliskapaczka\ApiClient\ApiCaller;

use Bliskapaczka\ApiClient\Logger;
use Bliskapaczka\ApiClient\Exception;

/**
 * Class ApiCaller
 *
 * @package            Bliskapaczka\ApiClient\ApiCaller
 * @codeCoverageIgnore That makes a HTTP request with the bpost API
 */
class ApiCaller
{

    /**
     * @var Logger
     */
    private $logger;

    /**
     * ApiCaller constructor.
     *
     * @param Logger $logger
     */
    public function __construct(Logger $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * @param array $options
     * @return bool
     */
    public function doCall(array $options)
    {
        $curl = curl_init();

        $options[CURLOPT_RETURNTRANSFER] = 1;

        curl_setopt_array($curl, $options);

        $error = curl_error($curl);
        if ($error) {
            throw new Exception($error, 1);
        }

        $response = curl_exec($curl);

        $responseCopy = json_decode($response);
        if ($responseCopy->error) {
            throw new Exception($responseCopy->error . ": " . $responseCopy->error_description, 1);
        }

// var_dump($response);
        return $response;
    }
}
