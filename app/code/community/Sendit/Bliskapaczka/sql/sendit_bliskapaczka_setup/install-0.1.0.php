<?php
/* @var $installer Mage_Sales_Model_Resource_Setup */
$installer = Mage::getResourceModel('sales/setup');

$entities = array(
 'quote_address',
'order_address',
);
$attributes = array(
 'pos_code',
'pos_operator',
);

$options = array(
    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'grid' => false,
    'visible' => true,
    'visible_on_front' => true,
    'required' => false,
    'is_user_defined' => true,
    'frontend_input' => 'varchar'
);

foreach ($attributes as $attributeCode) {
    foreach ($entities as $entity) {
        $installer->addAttribute($entity, $attributeCode, $options);
    }
}
