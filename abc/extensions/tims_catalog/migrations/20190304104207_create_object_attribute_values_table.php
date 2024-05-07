<?php
/**
* AbanteCart auto-generated migration file
*/


use Phinx\Migration\AbstractMigration;

class CreateObjectAttributeValuesTable extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {
        // create the table
       $table = $this->table('object_attribute_values');
        if(!$table->exists()) {
            $table->addColumn('object_id', 'integer')
                ->addColumn('object_type', 'string')
                ->addColumn('object_type_id', 'integer')
                ->addColumn('attribute_id', 'integer')
                ->addColumn('attribute_value', 'string')
                ->addColumn('attribute_name', 'string', ['default' => null, 'null' => true])
                ->addIndex(['object_id', 'object_type', 'attribute_id', 'object_type_id'], ['unique' => true])
                ->save();
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
