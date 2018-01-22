<?php

namespace Bliskapaczka\ApiClient\Mappers;

/**
 * Todoor Mapper class
 *
 * @author  Mateusz Koszutowski (mkoszutowski@divante.pl)
 * @version 0.1.0
 */
class Todoor
{
    private $allowedProperties = [
        'senderEmail',
        'receiverEmail',
        'senderPhoneNumber',
        'receiverPhoneNumber',
        'senderPostCode',
        'senderFirstName',
        'senderLastName',
        'senderStreet',
        'senderBuildingNumber',
        'senderFlatNumber',
        'senderCit',
        'receiverFirstName',
        'receiverLastName',
        'receiverStreet',
        'receiverBuildingNumber',
        'receiverFlatNumber',
        'receiverPostCode',
        'receiverCity',
        'operatorName',
        'insuranceValue',
        'additionalInformation',
        'parcel'
    ];

    /**
     * Magic method implementation
     *
     * @param string $property
     */
    public function __get($property)
    {
        if (in_array($property, $this->allowedProperties)) {
            return $this->$property;
        }
    }

    /**
     * Magic method implementation
     *
     * @param string $property
     * @param mixed $value
     */
    public function __set($property, $value)
    {
        if (in_array($property, $this->allowedProperties)) {
              $this->$property = $value;
        }

        return $this;
    }

    /**
     * Create new instance of this class with data mapped form array
     *
     * @param array $data
     */
    public static function createFromArray(array $data)
    {
        $todoor = new self();

        foreach ($data as $key => $value) {
            $todoor->$key = $value;
        }

        return $todoor;
    }

    /**
     * Validate data
     */
    public function validate()
    {
        /* Original Bliskapaczka validator regexps
        numer konta: /^\d{26}$/
        nip: /^\d{10}$/
        kod pocztowy: /^\d{2}\-\d{3}$/

        @NotBlank
        @Size(max = 30)
        private String senderFirstName;
        @NotBlank
        @Size(max = 30)
        private String senderLastName;
        @NotBlank
        @PhoneNumber
        private String senderPhoneNumber;
        @NotBlank
        @Email
        @Size(max = 60)
        private String senderEmail;
        @NotBlank
        @Size(max = 30)
        private String senderStreet;
        @NotBlank
        @Size(max = 10)
        private String senderBuildingNumber;
        @Size(max = 10)
        private String senderFlatNumber;
        @NotBlank
        @PostCode
        private String senderPostCode;
        @NotBlank
        @Size(max = 30)
        private String senderCity;

        @NotBlank
        @Size(max = 30)
        private String receiverFirstName;
        @NotBlank
        @Size(max = 30)
        private String receiverLastName;
        @NotBlank
        @PhoneNumber
        private String receiverPhoneNumber;
        @NotBlank
        @Email
        @Size(max = 60)
        private String receiverEmail;

        @NotNull
        private OperatorName operatorName;
        */

        # Email validation
        if ($this->senderEmail) {
            Order\Validator::email($this->senderEmail);
        }
        Order\Validator::email($this->receiverEmail);

        # Phone number validation
        if ($this->senderPhoneNumber) {
            Order\Validator::phone($this->senderPhoneNumber);
        }
        Order\Validator::phone($this->receiverPhoneNumber);


        # Post code validation
        if ($this->senderPostCode) {
            Order\Validator::postCode($this->senderPostCode);
        }

        # Parcel validation
        Order\Validator::parcel($this->parcel);

        # Rest of string properties
        $properties = [
            'receiverFirstName',
            'receiverLastName',
            'receiverStreet',
            'receiverBuildingNumber',
            'receiverFlatNumber',
            'receiverPostCode',
            'receiverCity',
            'operatorName'
        ];

        foreach ($properties as $property) {
            if (is_null($this->$property) || strlen($this->$property) == 0) {
                throw new \Bliskapaczka\ApiClient\Exception('Invalid ' . $property, 1);
            }
        }
    }
}
