<?php
/**
* AbanteCart auto-generated migration file
*/


use Phinx\Migration\AbstractMigration;

class ChangeOrderIdInCustomerTransactions extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {
        $this->execute("SET SQL_MODE = '';");
        $this->execute("SET FOREIGN_KEY_CHECKS = 0;");

        $prefix = $this->getAdapter()->getOption('table_prefix');

        $table = $this->table('customer_transactions');
        if($table->exists() && $table->hasColumn('order_id')) {
            $table->changeColumn( 'order_id', 'integer', ['null' => true, 'default' => null] )
                ->save();


            $update = "UPDATE ".$prefix."customer_transactions SET `order_id` = NULL WHERE `order_id` = 0;";
            $this->execute($update);

        }

        $this->execute("SET FOREIGN_KEY_CHECKS = 1;");

    }

    public function down()
    {

    }
}
