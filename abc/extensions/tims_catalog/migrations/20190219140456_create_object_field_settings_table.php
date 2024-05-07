<?php
/**
* AbanteCart auto-generated migration file
*/


use Phinx\Migration\AbstractMigration;

class CreateObjectFieldSettingsTable extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {
        $table = $this->table('object_field_settings');
        if(!$table->exists()) {
            $table->addColumn( 'object_type', 'string' )
                ->addColumn('object_type_id', 'integer')
                ->addColumn('object_field_name', 'string')
                ->addColumn('field_setting', 'string')
                ->addColumn( 'field_setting_value', 'string')
                ->addIndex(['object_type','object_type_id','object_field_name','field_setting'])
                ->save();
        }
    }

    public function down()
    {

    }
}
