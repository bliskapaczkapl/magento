<?php

require $GLOBALS['APP_DIR'] . '/code/community/Sendit/Bliskapaczka/Model/Adminhtml/ParcelSizeType.php';

use PHPUnit\Framework\TestCase;

class ParcelSizeTypeTest extends TestCase
{
    public function testClassExists()
    {
        $this->assertTrue(class_exists('Sendit_Bliskapaczka_Model_Adminhtml_ParcelSizeType'));
    }

    public function testClassHasMethods()
    {
        $this->assertTrue(method_exists('Sendit_Bliskapaczka_Model_Adminhtml_ParcelSizeType', 'toOptionArray'));
    }

    public function testToOptionArray()
    {
        $parcelSizeType = new Sendit_Bliskapaczka_Model_Adminhtml_ParcelSizeType();
        $options = $parcelSizeType->toOptionArray();

        $this->assertTrue(is_array($options));
        
        $this->assertTrue(is_array($options[0]));
        $this->assertEquals($options[0]['value'], 1);
        $this->assertEquals($options[0]['label'], 'Fixed');
    }
}
