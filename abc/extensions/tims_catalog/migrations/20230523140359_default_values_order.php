<?php
/**
 * AbanteCart auto-generated migration file
 */


use Phinx\Migration\AbstractMigration;

class DefaultValuesOrder extends AbstractMigration
{
    public function up()
    {
        $tableAdapter = new Phinx\Db\Adapter\TablePrefixAdapter($this->getAdapter());
        $full_table_name = $tableAdapter->getAdapterTableName('orders');
        $sql = "alter table " . $full_table_name . "
                    alter column payment_method_data set default '';";
        $this->execute($sql);
    }

    public function down()
    {

    }
}