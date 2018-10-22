<?php
/** @var Mage_Sales_Model_Resource_Setup $installer */
$installer = $this;

$attributes = array(
    'error_reason'
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
