<?php
/**
* AbanteCart auto-generated migration file
*/


use Phinx\Migration\AbstractMigration;

class AddStoreIdToCustomListsTable extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {
       $table = $this->table('custom_lists');
        if($table->exists() && !$table->hasColumn('store_id')) {
            $table->addColumn( 'store_id', 'integer', ['default' => 0] )
                ->save();
        }
    }

    public function down()
    {


    }
}
