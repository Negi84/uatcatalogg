<?php
/**
 * AbanteCart auto-generated migration file
 */

use Phinx\Migration\AbstractMigration;

class SkuUniqueChanges extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {

        $table = $this->table('products');
        $table->changeColumn(
            'sku', 'string', ['limit' => 64, 'null' => true]
        )->update();

        $tableAdapter = new Phinx\Db\Adapter\TablePrefixAdapter($this->getAdapter());
        $sql = "
        UPDATE `".$tableAdapter->getAdapterTableName('products')."` SET sku=NULL WHERE sku=''; 
        ALTER TABLE `".$tableAdapter->getAdapterTableName('products')."` 
        ADD UNIQUE INDEX `sku_unique_idx` (`sku` ASC)";
        $this->execute($sql);

        $table = $this->table('product_option_values');
        $table->changeColumn(
            'sku', 'string', ['limit' => 64, 'null' => true]
        )->update();

        $sql = "
        UPDATE `".$tableAdapter->getAdapterTableName('product_option_values')."` SET sku=NULL WHERE sku=''; 
        ALTER TABLE `".$tableAdapter->getAdapterTableName('product_option_values')."` 
        ADD UNIQUE INDEX `sku_unique_idx` (`sku` ASC)";
        $this->execute($sql);

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