<?php
/** @var Mage_Sales_Model_Resource_Setup $installer */
$installer = $this;

$entities = array(
    'quote_address',
    'order_address',
);

$attribute = 'pos_code_description';

$options = array(
    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'grid' => false,
    'visible' => true,
    'visible_on_front' => true,
    'required' => false,
    'is_user_defined' => true,
    'frontend_input' => 'varchar'
);

foreach ($entities as $entity) {
    $installer->addAttribute($entity, $attribute, $options);
}

$attributes = array(
    'pos_code',
    'pos_operator',
    'pos_code_description'
);

foreach ($attributes as $attributeCode) {
    $installer->getConnection()->addColumn(
        $installer->getTable('sendit_bliskapaczka/order'),
        $attributeCode,
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'grid' => false,
            'visible' => true,
            'visible_on_front' => true,
            'required' => false,
            'is_user_defined' => true,
            'frontend_input' => 'varchar',
            'comment' => str_replace('_', ' ', $attributeCode)
        )
    ); 
}
