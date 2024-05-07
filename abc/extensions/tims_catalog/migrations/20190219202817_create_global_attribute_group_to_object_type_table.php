<?php
/**
* AbanteCart auto-generated migration file
*/


use Phinx\Migration\AbstractMigration;

class CreateGlobalAttributeGroupToObjectTypeTable extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {
        $table = $this->table('global_attribute_group_to_object_type');
        if(!$table->exists()) {
            $table->addColumn( 'attribute_group_id', 'integer' )
                ->addColumn('object_type_id', 'integer')
                ->addIndex(['attribute_group_id','object_type_id'])
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
