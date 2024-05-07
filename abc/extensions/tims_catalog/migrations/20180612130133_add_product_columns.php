<?php
/**
 * AbanteCart auto-generated migrationfile
 */

use Phinx\Migration\AbstractMigration;

class AddProductColumns extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {
        $table = $this->table('products');
        $table->addColumn('uplift_id', 'integer', ['after' => 'cost'])->update();
        $table->addColumn('product_type', 'string', ['limit' => 1, 'after' => 'uplift_id'])->update();
        $table->addColumn('sites', 'text', ['limit' => 1500, 'after' => 'product_type'])->update();

    }

    public function down()
    {
        /*
         $table = $this->table('new table');
         if($table->exists()) {
             $table->drop();
         }
        */

    }
}