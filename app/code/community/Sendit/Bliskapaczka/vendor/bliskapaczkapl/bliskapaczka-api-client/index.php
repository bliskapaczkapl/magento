<?php

require __DIR__ . '/vendor/autoload.php';

$apiKey = 'e5814ba2-2dc6-4f31-bfa9-2afd4bc171a9';
$apiClient = new Bliskapaczka\ApiClient\Bliskapaczka\Order($apiKey, 'test');

$orderData = [
    "senderFirstName" => "string",
    "senderLastName" => "string",
    "senderPhoneNumber" => "606555433",
    "senderEmail" => "bob@example.com",
    "senderStreet" => "string",
    "senderBuildingNumber" => "string",
    "senderFlatNumber" => "string",
    "senderPostCode" => "54-130",
    "senderCity" => "string",
    "receiverFirstName" => "string",
    "receiverLastName" => "string",
    "receiverPhoneNumber" => "600555432",
    "receiverEmail" => "eva@example.com",
    "operatorName" => "INPOST",
    "destinationCode" => "KRA010",
    "postingCode" => "KOS01L",
    "codValue" => 0,
    "insuranceValue" => 0,
    "additionalInformation" => "string",
    "parcel" => [
        "dimensions" => [
            "height" => 20,
            "length" => 20,
            "width" => 20,
            "weight" => 2
        ]
    ]
];
var_dump($apiClient->create($orderData));

$pricingData = [
    "parcel" => [
        "dimensions" => [
            "height" => 20,
            "length" => 20,
            "width" => 20,
            "weight" => 2
        ]
    ]
];
var_dump($apiClient->getPricing($pricingData));