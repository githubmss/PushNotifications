<?php

namespace Mmsbuilder\Pushnotification\Setup;
 
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
 
/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
 
        $installer->startSetup();
 
        /*
         * Create table 'notification'
         */


        $installer->getConnection()->addColumn(
        $installer->getTable('sales_order'),
            'device_data',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'comment' => 'Device Data'
            ]
        );
 
        $table = $installer->getConnection()->newTable(
            $installer->getTable('Mmsbuilder_Pushnotification')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Notification Id'
        )->addColumn(
            'user_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'User Id'
        )->addColumn(
            'registration_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '255',
            ['nullable' => false],
            'Registration Id'
        )->addColumn(
            'device_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '255',
            ['nullable' => false],
            'Registration Id'
        )->addColumn(
            'device_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            30,
            ['nullable' => false],
            'Device Type'
        )->addColumn(
            'create_date',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
            ['nullable' => false],
            [],
            'created date'
        )->addColumn(
            'update_date',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false],
            'Updated Date Time'
        )->addColumn(
            'app_status',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false],
            'Status Of App'
        );
 
        $installer->getConnection()->createTable($table);
        $installer->endSetup();
    }
}
