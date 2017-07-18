<?php

use PHPUnit\Framework\TestCase;

class SystemXmlTest extends TestCase
{
    protected $filePath;
    protected $config;

    protected function setUp()
    {
        $this->filePath = $GLOBALS['APP_DIR'] . '/code/community/Sendit/Bliskapaczka/etc/system.xml';
        $this->config = new SimpleXMLElement(file_get_contents($this->filePath));
    }

    public function testFileExists()
    {
        $this->assertTrue(file_exists($this->filePath));
    }

    public function testParcelConfiguration()
    {
        $fields = $this->config->sections->carriers->groups->sendit_bliskapaczka->fields;

        $this->assertEquals('Parcel size type', $fields->parcel_size_type->label);
        $this->assertEquals('Fixed parce type size X (cm)', $fields->parcel_size_type_fixed_size_x->label);
        $this->assertEquals('Fixed parce type size Y (cm)', $fields->parcel_size_type_fixed_size_y->label);
        $this->assertEquals('Fixed parce type size Z (cm)', $fields->parcel_size_type_fixed_size_z->label);
        $this->assertEquals('Fixed parce type weight (kg)', $fields->parcel_size_type_fixed_size_weight->label);
    }

    public function testSenderDataConfiguration()
    {
        $fields = $this->config->sections->carriers->groups->sendit_bliskapaczka->fields;

        $this->assertEquals('Sender email', $fields->sender_email->label);
        $this->assertEquals('Sender first name', $fields->sender_first_name->label);
        $this->assertEquals('Sender last name', $fields->sender_last_name->label);
        $this->assertEquals('Sender phone number', $fields->sender_phone_number->label);
        $this->assertEquals('Sender street', $fields->sender_street->label);
        $this->assertEquals('Sender building number', $fields->sender_building_number->label);
        $this->assertEquals('Sender flat number', $fields->sender_flat_number->label);
        $this->assertEquals('Sender post code', $fields->sender_post_code->label);
        $this->assertEquals('Sender city', $fields->sender_city->label);
    }
}
