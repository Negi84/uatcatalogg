<?php
/**
* AbanteCart auto-generated migration file
*/

use abc\core\engine\Registry;
use Phinx\Migration\AbstractMigration;

class DeliveryBandPerNode extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {
        $db = Registry::db();
        $this->execute("SET SQL_MODE = '';");
        $table = $this->table('products');
        if ($table->hasColumn('product_type')) {
            $table
                ->changeColumn( 'product_type', 'text' )
                ->changeColumn( 'uplift_id', 'text' )
            ->save();

            $prefix = $this->getAdapter()->getOption('table_prefix');

            $rows = $this->fetchAll("SELECT * FROM ".$prefix."products");

            foreach ($rows as $row) {
                $update = "UPDATE ".$prefix."products 
                            SET 
                                `product_type` = '".$db->escape( serialize(['bidfood' => $row['product_type']]) )."', 
                                `uplift_id` = '".$db->escape( serialize(['bidfood' => $row['uplift_id']]) )."' 
                            WHERE `product_id` = ".(int)$row['product_id'].";";
                $this->execute($update);
            }

        }
    }

    public function down()
    {

    }
}