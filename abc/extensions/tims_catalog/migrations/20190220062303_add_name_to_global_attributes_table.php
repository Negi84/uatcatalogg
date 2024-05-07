<?php
/**
* AbanteCart auto-generated migration file
*/


use Phinx\Migration\AbstractMigration;

class AddNameToGlobalAttributesTable extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {
        $table = $this->table('global_attributes');
        if($table->exists() && !$table->hasColumn('name')) {
            $table->addColumn( 'name', 'string',  ['after' => 'attribute_type_id'] )
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
