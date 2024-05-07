<?php
/**
* AbanteCart auto-generated migration file
*/


use Phinx\Migration\AbstractMigration;

class AddGroupIdToSettingsTable extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {
        $table = $this->table('settings');
        if($table->exists() && !$table->hasColumn('group_id')) {
            $table->addColumn( 'group_id', 'integer', ['after' => 'group'] )
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
