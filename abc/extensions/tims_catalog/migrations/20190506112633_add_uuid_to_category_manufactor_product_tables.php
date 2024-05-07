<?php
/**
 * AbanteCart auto-generated migration file
 */

use Phinx\Migration\AbstractMigration;

class AddUuidToCategoryManufactorProductTables extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {
        $tableNames = [
            'products'      => 'product_id',
            'categories'    => 'category_id',
            'manufacturers' => 'manufacturer_id',
        ];
        foreach ($tableNames as $tableName => $keyField) {
            $table = $this->table($tableName);
            if ($table->exists() && !$table->hasColumn('uuid')) {
                $table->addColumn('uuid', 'string', ['after' => $keyField, 'null' => true, 'default' => null])
                    ->addIndex('uuid')
                    ->save();
            }
        }
    }

    public function down()
    {
        /*
         $table = $this->table('table_name_with_prefix');
         if($table->exists()) {
             $table->drop();
         }
        */

    }
}
