<?php
/**
* AbanteCart auto-generated migration file
*/


use Phinx\Migration\AbstractMigration;

class ChangeOnlineCustomersTabls extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {
       $table = $this->table('online_customers');
        if($table->exists() && $table->hasColumn('customer_id')) {
            $table->changeColumn('customer_id', 'integer', ['null'=> true, 'default'=>null])
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
