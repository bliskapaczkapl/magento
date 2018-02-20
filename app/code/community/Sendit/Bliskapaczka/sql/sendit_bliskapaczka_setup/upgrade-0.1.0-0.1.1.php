<?php

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();

$conn = $installer->getConnection();

$orderTable =
    $conn
        ->newTable($installer->getTable('sendit_bliskapaczka/order'))
        ->addColumn(
            'entity_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            [
                'auto_increment' => true,
                'unsigned'       => true,
                'nullable'       => false,
                'primary'        => true,
            ],
            'Order\'s identifier'
        )
        ->addColumn(
            'order_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            [
                'unsigned' => true,
                'nullable' => false,
            ],
            'Sales flat order Id'
        )
        ->addColumn(
            'number',
            Varien_Db_Ddl_Table::TYPE_VARCHAR,
            null,
            [
                'nullable' => true,
                'default'  => '',
            ],
            'Number'
        )
        ->addColumn(
            'status',
            Varien_Db_Ddl_Table::TYPE_VARCHAR,
            null,
            [
                'nullable' => true,
                'default'  => '',
            ],
            'Status'
        )
        ->addColumn(
            'delivery_type',
            Varien_Db_Ddl_Table::TYPE_VARCHAR,
            null,
            [
                'nullable' => true,
                'default'  => '',
            ],
            'Delivery type'
        )
        ->addColumn(
            'creation_date',
            Varien_Db_Ddl_Table::TYPE_DATETIME,
            null,
            [
                'nullable' => true
            ],
            'Creation date'
        )
        ->addColumn(
            'advice_date',
            Varien_Db_Ddl_Table::TYPE_DATETIME,
            null,
            [
                'nullable' => true
            ],
            'Advice date'
        )
        ->addColumn(
            'tracking_number',
            Varien_Db_Ddl_Table::TYPE_VARCHAR,
            null,
            [
                'nullable' => true,
                'default'  => '',
            ],
            'Tracking number'
        )
        ->addForeignKey(
            $installer->getFkName('sendit_bliskapaczka/order', 'order_id', 'sales/order', 'entity_id'),
            'order_id',
            $installer->getTable('sales/order'),
            'entity_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE,
            Varien_Db_Ddl_Table::ACTION_CASCADE
        )->setComment('Bliskapaczka order entity');

$conn->createTable($orderTable);

