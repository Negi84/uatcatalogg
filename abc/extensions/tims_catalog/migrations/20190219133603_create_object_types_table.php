<?php
/**
* AbanteCart auto-generated migration file
*/


use Phinx\Migration\AbstractMigration;

class CreateObjectTypesTable extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {
      $table = $this->table('object_types', ['id' => false, 'primary_key' => 'object_type_id']);
        if(!$table->exists()) {
            $table->addColumn( 'object_type_id', 'integer', ['identity' => true] )
                ->addColumn('object_type', 'string')
                ->addColumn('status', 'boolean', ['null' => true])
                ->addColumn('sort_order', 'integer', ['null' => true])
                ->addColumn( 'date_added', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addColumn( 'date_modified', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
                ->addColumn( 'date_deleted', 'timestamp', ['null' => true])
                ->addColumn('stage_id', 'integer', ['null' => true])
                ->save();
        }
    }

    public function down()
    {

    }
}
