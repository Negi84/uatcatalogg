<?php
/**
* AbanteCart auto-generated migration file
*/


use Phinx\Migration\AbstractMigration;

class RemoveProductForeignKeyInOrderProductTable extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {
       $prefix = $this->getAdapter()->getOption('table_prefix');

       $table = $this->table('order_products');
        if($table->exists() && $table->hasForeignKey('product_id')) {
            $table->dropForeignKey('product_id')->save();
        }
    }

    public function down()
    {

    }
}
