<?php
/**
 * Bring plugin for Craft CMS 3.x
 *
 * Integrate Bring/Posten with Craft Commerce
 *
 * @link      https://superbig.co
 * @copyright Copyright (c) 2018 Superbig
 */

namespace superbig\bring\migrations;

use superbig\bring\Bring;

use Craft;
use craft\config\DbConfig;
use craft\db\Migration;
use superbig\bring\records\BoxRecord;
use superbig\bring\records\ShipmentRecord;

/**
 * @author    Superbig
 * @package   Bring
 * @since     1.0.0
 */
class Install extends Migration
{
    // Public Properties
    // =========================================================================

    /**
     * @var string The database driver to use
     */
    public $driver;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        if ($this->createTables()) {
            //$this->createIndexes();
            $this->addForeignKeys();
            // Refresh the db schema caches
            Craft::$app->db->schema->refresh();
            $this->insertDefaultData();
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->removeTables();

        return true;
    }

    // Protected Methods
    // =========================================================================

    /**
     * @return bool
     */
    protected function createTables()
    {
        $tablesCreated = false;

        $tableSchema = Craft::$app->db->schema->getTableSchema(BoxRecord::TABLE_NAME);
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                BoxRecord::TABLE_NAME,
                [
                    'id'          => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid'         => $this->uid(),
                    'siteId'      => $this->integer()->notNull(),
                    'name'        => $this->string()->notNull(),
                    'handle'      => $this->string()->notNull(),
                    'outerWidth'  => $this->integer()->null(),
                    'outerLength' => $this->integer()->null(),
                    'outerHeight' => $this->integer()->null(),
                    'outerDepth'  => $this->integer()->null(),
                    'innerWidth'  => $this->integer()->null(),
                    'innerLength' => $this->integer()->null(),
                    'innerHeight' => $this->integer()->null(),
                    'innerDepth'  => $this->integer()->null(),
                    'emptyWeight' => $this->integer()->null(),
                    'maxWeight'   => $this->integer()->null(),
                    'enabled'     => $this->boolean()->defaultValue(true),
                ]
            );
        }

        $tableSchema = Craft::$app->db->schema->getTableSchema(ShipmentRecord::TABLE_NAME);
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                ShipmentRecord::TABLE_NAME,
                [
                    'id'                => $this->primaryKey(),
                    'dateCreated'       => $this->dateTime()->notNull(),
                    'dateUpdated'       => $this->dateTime()->notNull(),
                    'uid'               => $this->uid(),
                    'orderId'           => $this->integer()->notNull(),
                    'consignmentNumber' => $this->string()->notNull(),
                    'labelUrl'          => $this->string()->notNull(),
                    'request'           => $this->text(),
                    'response'          => $this->text(),
                    'shipped'           => $this->boolean()->defaultValue(true),
                    'returned'          => $this->boolean()->defaultValue(true),
                    'type'              => $this->string()->notNull()->defaultValue('shipment'),
                ]
            );
        }

        return $tablesCreated;
    }

    /**
     * @return void
     */
    protected function createIndexes()
    {
        $this->createIndex(
            $this->db->getIndexName(
                BoxRecord::TABLE_NAME,
                'some_field',
                true
            ),
            BoxRecord::TABLE_NAME,
            'some_field',
            true
        );
        // Additional commands depending on the db driver
        switch ($this->driver) {
            case DbConfig::DRIVER_MYSQL:
                break;
            case DbConfig::DRIVER_PGSQL:
                break;
        }
    }

    /**
     * @return void
     */
    protected function addForeignKeys()
    {
        $this->addForeignKey(
            $this->db->getForeignKeyName(BoxRecord::TABLE_NAME, 'siteId'),
            BoxRecord::TABLE_NAME,
            'siteId',
            '{{%sites}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(ShipmentRecord::TABLE_NAME, 'orderId'),
            ShipmentRecord::TABLE_NAME,
            'orderId',
            '{{%elements}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * @return void
     */
    protected function insertDefaultData()
    {
    }

    /**
     * @return void
     */
    protected function removeTables()
    {
        $this->dropTableIfExists(BoxRecord::TABLE_NAME);
    }
}
