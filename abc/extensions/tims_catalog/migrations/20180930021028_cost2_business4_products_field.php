<?php
/**
* AbanteCart auto-generated migration file
*/


use Phinx\Migration\AbstractMigration;

class Cost2Business4ProductsField extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {


        $tables = ["products", "products_chnglog"];
        $this->execute("SET SQL_MODE = '';");
        foreach ($tables as $tableName) {
            $table = $this->table($tableName);
            if ($table->exists()) {
                if (!$table->hasColumn('cost_to_business')) {
                    $table->addColumn(
                        'cost_to_business',
                        'decimal',
                        ['precision' => 15, 'scale' => 4])
                        ->update();
                    $table->save();
                }
            }
        }
        $tables = ["order_products", "order_products_chnglog"];
        foreach ($tables as $tableName) {
            $table = $this->table($tableName);
            if ($table->exists()) {
                if (!$table->hasColumn('cost_to_business')) {
                    $table->addColumn(
                        'cost_to_business',
                        'decimal',
                        ['precision' => 15, 'scale' => 4])
                        ->update();
                    $table->save();
                }
            }
        }
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