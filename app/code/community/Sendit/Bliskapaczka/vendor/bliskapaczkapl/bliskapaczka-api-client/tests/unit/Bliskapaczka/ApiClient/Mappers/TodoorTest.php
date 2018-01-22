<?php

namespace Bliskapaczka\ApiClient\Mappers\Todoor;

use Bliskapaczka\ApiClient\Mappers\Todoor;
use PHPUnit\Framework\TestCase;

class TodoorTest extends TestCase
{
    protected function setUp()
    {
        $this->todoorData = [
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
            "receiverStreet" => "Testowa",
            "receiverBuildingNumber" => "1",
            "receiverFlatNumber" => '11',
            "receiverPostCode" => "12-345",
            "receiverCity" => "Testowe",
            "operatorName" => "DPD",
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
    }

    public function testClassExists()
    {
        $this->assertTrue(class_exists('Bliskapaczka\ApiClient\Mappers\Todoor'));
    }

    public function testCreateFromArray()
    {
        $todoor = Todoor::createFromArray($this->todoorData);
        $todoor->validate();

        $this->assertEquals('Bliskapaczka\ApiClient\Mappers\Todoor', get_class($todoor));
    }

    /**
     * @expectedException Bliskapaczka\ApiClient\Exception
     * @expectedExceptionMessage Invalid phone number
     */
    public function testReceiverPhoneNumberValidation()
    {
        $this->todoorData['receiverPhoneNumber'] = 'string';

        $todoor = Todoor::createFromArray($this->todoorData);
        $todoor->validate();
    }

    /**
     * @expectedException Bliskapaczka\ApiClient\Exception
     * @expectedExceptionMessage Invalid post code
     */
    public function testSenderPostCodeValidation()
    {
        $this->todoorData['senderPostCode'] = 'string';

        $todoor = Todoor::createFromArray($this->todoorData);
        $todoor->validate();
    }

    /**
     * @expectedException Bliskapaczka\ApiClient\Exception
     * @expectedExceptionMessage Invalid parcel
     */
    public function testParcelsValidation()
    {
        $this->todoorData['parcel'] = 'string';

        $todoor = Todoor::createFromArray($this->todoorData);
        $todoor->validate();
    }

    /**
     * @expectedException Bliskapaczka\ApiClient\Exception
     * @expectedExceptionMessageRegExp /Invalid \w+/
     */
    public function testReceiverFirstNameValidation()
    {
        $this->todoorData['receiverFirstName'] = '';

        $todoor = Todoor::createFromArray($this->todoorData);
        $todoor->validate();
    }

    /**
     * @expectedException Bliskapaczka\ApiClient\Exception
     * @expectedExceptionMessageRegExp /Invalid \w+/
     */
    public function testReceiverLastNameValidation()
    {
        $this->todoorData['receiverLastName'] = '';

        $todoor = Todoor::createFromArray($this->todoorData);
        $todoor->validate();
    }

    /**
     * @expectedException Bliskapaczka\ApiClient\Exception
     * @expectedExceptionMessageRegExp /Invalid \w+/
     */
    public function testReceiverStreetValidation()
    {
        $this->todoorData['receiverStreet'] = '';

        $todoor = Todoor::createFromArray($this->todoorData);
        $todoor->validate();
    }

    /**
     * @expectedException Bliskapaczka\ApiClient\Exception
     * @expectedExceptionMessageRegExp /Invalid \w+/
     */
    public function testReceiverBuildingNumberValidation()
    {
        $this->todoorData['receiverBuildingNumber'] = '';

        $todoor = Todoor::createFromArray($this->todoorData);
        $todoor->validate();
    }

    /**
     * @expectedException Bliskapaczka\ApiClient\Exception
     * @expectedExceptionMessageRegExp /Invalid \w+/
     */
    public function testReceiverFlatNumberValidation()
    {
        $this->todoorData['receiverFlatNumber'] = '';

        $todoor = Todoor::createFromArray($this->todoorData);
        $todoor->validate();
    }

    /**
     * @expectedException Bliskapaczka\ApiClient\Exception
     * @expectedExceptionMessageRegExp /Invalid \w+/
     */
    public function testReceiverPostCodeValidation()
    {
        $this->todoorData['receiverPostCode'] = '';

        $todoor = Todoor::createFromArray($this->todoorData);
        $todoor->validate();
    }

    /**
     * @expectedException Bliskapaczka\ApiClient\Exception
     * @expectedExceptionMessageRegExp /Invalid \w+/
     */
    public function testReceiverCityValidation()
    {
        $this->todoorData['receiverCity'] = '';

        $todoor = Todoor::createFromArray($this->todoorData);
        $todoor->validate();
    }

    /**
     * @expectedException Bliskapaczka\ApiClient\Exception
     * @expectedExceptionMessageRegExp /Invalid \w+/
     */
    public function testOperatorNameValidation()
    {
        $this->todoorData['operatorName'] = '';

        $todoor = Todoor::createFromArray($this->todoorData);
        $todoor->validate();
    }

    /**
     * @expectedException Bliskapaczka\ApiClient\Exception
     * @expectedExceptionMessage Dimesnion must be greater than 0
     */
    public function testParcelDimensionsValidation()
    {
        $this->todoorData['parcel']['dimensions']['height'] = 0;

        $todoor = Todoor::createFromArray($this->todoorData);
        $todoor->validate();

        $this->todoorData['parcel']['dimensions']['height'] = -1;

        $todoor = Todoor::createFromArray($this->todoorData);
        $todoor->validate();
    }
}
