<?php

require $GLOBALS['APP_DIR'] . '/code/community/Sendit/Bliskapaczka/Model/Mapper/Admin.php';

use PHPUnit\Framework\TestCase;

class AdminTest extends TestCase
{

    protected function setUp()
    {
        $this->senderFirstName = 'Zenek';
        $this->senderLastName = 'Bliskopaczki';
        $this->senderPhoneNumber = '504 445 665';
        $this->senderEmail = 'zenek.bliskopaczki@sendit.pl';
        $this->senderStreet = 'Ulicowa';
        $this->senderBuildingNumber = '11b';
        $this->senderFlatNumber = '';
        $this->senderPostCode = '77-100';
        $this->senderCity = 'Miastowe';

        $this->postData = array(
            'fields' => array(
                'active' => array('value' => '1', ),
                'title' => array( 'value' => 'Bliska Paczka', ),
                'name' => array( 'value' => '', ), 
                'bliskapaczkaapikey' => array( 'value' => 'key-key-key-key', ),
                'test_mode' => array( 'value' => '1', ),
                'google_map_api_key' => array( 'value' => '', ),
                'parcel_size_type' => array( 'value' => '1', ),
                'parcel_size_type_fixed_size_x' => array( 'value' => '12', ),
                'parcel_size_type_fixed_size_y' => array( 'value' => '12', ),
                'parcel_size_type_fixed_size_z' => array( 'value' => '16', ),
                'parcel_size_type_fixed_size_weight' => array ('value' => '1', ),
                'sender_email' => array( 'value' => $this->senderEmail, ),
                'sender_first_name' => array( 'value' => $this->senderFirstName, ),
                'sender_last_name' => array( 'value' => $this->senderLastName, ),
                'sender_phone_number' => array( 'value' => $this->senderPhoneNumber, ),
                'sender_street' => array( 'value' => $this->senderStreet, ),
                'sender_building_number' => array( 'value' => $this->senderBuildingNumber, ),
                'sender_flat_number' => array( 'value' => $this->senderFlatNumber, ),
                'sender_post_code' => array( 'value' => $this->senderPostCode, ),
                'sender_city' => array( 'value' => $this->senderCity, ),
                'sallowspecific' => array( 'value' => '0', ),
                'specificcountry' => array( 'value' => '', ),
                'showmethod' => array( 'value' => '0', ),
                'sort_order' => array( 'value' => '10', ),
            ),
        );

        $this->helperMock = $this->getMockBuilder(Sendit_Bliskapaczka_Helper_Data::class)
                                     ->disableOriginalConstructor()
                                     ->disableOriginalClone()
                                     ->disableArgumentCloning()
                                     ->disallowMockingUnknownTypes()
                                     ->setMethods(
                                         array(
                                             'telephoneNumberCleaning'
                                         )
                                     )
                                     ->getMock();

        $this->helperMock->method('telephoneNumberCleaning')
            ->with($this->equalTo('504 445 665'))
            ->will($this->returnValue('504445665'));
    }

    public function testClassExists()
    {
        $this->assertTrue(class_exists('Sendit_Bliskapaczka_Model_Mapper_Admin'));
    }

    public function testClassHasMethods()
    {
        $this->assertTrue(method_exists('Sendit_Bliskapaczka_Model_Mapper_Admin', 'getData'));
    }

    public function testMapperForSenderFirstName()
    {
        $mapper = new Sendit_Bliskapaczka_Model_Mapper_Admin();
        $data = $mapper->getData($this->postData, $this->helperMock);

        $this->assertEquals($this->senderFirstName, $data['senderFirstName']);
    }

    public function testMapperForSenderLastName()
    {
        $mapper = new Sendit_Bliskapaczka_Model_Mapper_Admin();
        $data = $mapper->getData($this->postData, $this->helperMock);

        $this->assertEquals($this->senderLastName, $data['senderLastName']);
    }

    public function testMapperForSenderPhoneNumber()
    {
        $mapper = new Sendit_Bliskapaczka_Model_Mapper_Admin();
        $data = $mapper->getData($this->postData, $this->helperMock);

        $this->assertEquals('504445665', $data['senderPhoneNumber']);
    }

    public function testMapperForSenderEmail()
    {
        $mapper = new Sendit_Bliskapaczka_Model_Mapper_Admin();
        $data = $mapper->getData($this->postData, $this->helperMock);

        $this->assertEquals($this->senderEmail, $data['senderEmail']);
    }

    public function testMapperForSenderStreet()
    {
        $mapper = new Sendit_Bliskapaczka_Model_Mapper_Admin();
        $data = $mapper->getData($this->postData, $this->helperMock);

        $this->assertEquals($this->senderStreet, $data['senderStreet']);
    }

    public function testMapperForSenderBuildingNumber()
    {
        $mapper = new Sendit_Bliskapaczka_Model_Mapper_Admin();
        $data = $mapper->getData($this->postData, $this->helperMock);

        $this->assertEquals($this->senderBuildingNumber, $data['senderBuildingNumber']);
    }

    public function testMapperForSenderFlatNumber()
    {
        $mapper = new Sendit_Bliskapaczka_Model_Mapper_Admin();
        $data = $mapper->getData($this->postData, $this->helperMock);

        $this->assertEquals($this->senderFlatNumber, $data['senderFlatNumber']);
    }

    public function testMapperForSenderPostCode()
    {
        $mapper = new Sendit_Bliskapaczka_Model_Mapper_Admin();
        $data = $mapper->getData($this->postData, $this->helperMock);

        $this->assertEquals($this->senderPostCode, $data['senderPostCode']);
    }

    public function testMapperForSenderCity()
    {
        $mapper = new Sendit_Bliskapaczka_Model_Mapper_Admin();
        $data = $mapper->getData($this->postData, $this->helperMock);

        $this->assertEquals($this->senderCity, $data['senderCity']);
    }
}
