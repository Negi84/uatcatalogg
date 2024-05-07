<?php
/**
* AbanteCart auto-generated migration file
*/


use Phinx\Migration\AbstractMigration;

class AddProductTypeIdToProductsTable extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {
        $this->execute("SET SQL_MODE = '';");
        $table = $this->table('products');
        if($table->exists() && !$table->hasColumn('product_type_id')) {
            $table->addColumn( 'product_type_id', 'integer',  ['after' => 'call_to_order'] )
                ->save();
        }
    }

    public function down()
    {

    }
}
