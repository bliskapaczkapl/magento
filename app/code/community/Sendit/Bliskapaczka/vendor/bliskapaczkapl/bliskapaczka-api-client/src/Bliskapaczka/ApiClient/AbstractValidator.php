<?php

namespace Bliskapaczka\ApiClient;

use Psr\Log\LoggerInterface;
use Bliskapaczka\ApiClient\Exception;

/**
 * Bliskapaczka class
 *
 * @author  Mateusz Koszutowski (mkoszutowski@divante.pl)
 * @version 0.1.0
 */
abstract class AbstractValidator
{
    const PHONE_NUMBER_PATTERN = '/^(5[0137]|6[069]|7[2389]|88)\d{7}$/';

    /**
     * Set data to validata
     *
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * Basic validation for data
     */
    public function basicValidation()
    {
        foreach ($this->properties as $property => $settings) {
            if (isset($settings['notblank'])
                && isset($settings['notblank']) === true
                && (is_null($this->data[$property]) || strlen($this->data[$property]) == 0)
            ) {
                throw new Exception('Invalid ' . $property, 1);
            }
            
            if (isset($settings['maxlength'])
                && $settings['maxlength'] > 0
                && strlen($this->data[$property]) > $settings['maxlength']
            ) {
                throw new Exception('Invalid ' . $property, 1);
            }
        }
    }

    /**
     * Set of specific method for order validation
     */
    public function orderValidation()
    {
        # Email validation
        if ($this->data['senderEmail']) {
            self::email($this->data['senderEmail']);
        }
        if ($this->data['receiverEmail']) {
            self::email($this->data['receiverEmail']);
        }

        # Phone number validation
        if ($this->data['senderPhoneNumber']) {
            self::phone($this->data['senderPhoneNumber']);
        }
        if ($this->data['receiverPhoneNumber']) {
            self::phone($this->data['receiverPhoneNumber']);
        }

        # Post code validation
        if ($this->data['senderPostCode']) {
            self::postCode($this->data['senderPostCode']);
        }
        if (isset($this->data['receiverPostCode']) && $this->data['receiverPostCode']) {
            self::postCode($this->data['receiverPostCode']);
        }
        # Parcel validation
        self::parcel($this->data['parcel']);
    }

    /**
     * Validate email address
     *
     * @param string $data
     */
    public static function email($data)
    {
        if (filter_var($data, FILTER_VALIDATE_EMAIL) == false) {
            throw new \Bliskapaczka\ApiClient\Exception('Invalid email', 1);
        }

        return true;
    }

    /**
     * Validate phone number
     *
     * @param string $data
     */
    public static function phone($data)
    {
        preg_match(self::PHONE_NUMBER_PATTERN, $data, $phoneNumberMatches);

        if (!is_array($phoneNumberMatches) || count($phoneNumberMatches) == 0) {
            throw new \Bliskapaczka\ApiClient\Exception('Invalid phone number', 1);
        }

        return true;
    }

    /**
     * Validate postcode
     *
     * @param string $data
     */
    public static function postCode($data)
    {
        preg_match('/^\d{2}\-\d{3}$/', $data, $matches);

        if (!is_array($matches) || count($matches) == 0) {
            throw new \Bliskapaczka\ApiClient\Exception('Invalid post code', 1);
        }

        return true;
    }

    /**
     * Validate parcel
     *
     * @param array $data
     */
    public static function parcel($data)
    {
        if (!is_array($data) || !array_key_exists('dimensions', $data)) {
            throw new Exception('Invalid parcel', 1);
        }

        $dimensions = ['height', 'length', 'width', 'weight'];

        # Parcel dimesnsions should be graten than 0
        foreach ($dimensions as $dimension) {
            if ($data['dimensions'][$dimension] <= 0) {
                throw new \Bliskapaczka\ApiClient\Exception('Dimesnion must be greater than 0', 1);
            }
        }
    }
}
