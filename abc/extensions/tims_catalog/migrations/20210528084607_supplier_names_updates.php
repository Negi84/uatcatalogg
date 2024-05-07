<?php
/**
* AbanteCart auto-generated migration file
*/


use Phinx\Migration\AbstractMigration;

class SupplierNamesUpdates extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {
        $tableAdapter = new Phinx\Db\Adapter\TablePrefixAdapter($this->getAdapter());
        $full_table_name = $tableAdapter->getAdapterTableName('products');

        $this->execute(
            "UPDATE ".$full_table_name." 
            SET supplier='LASTMINUTE' 
            WHERE supplier = 'lastminute' AND license=1;"
        );
        $this->execute(
            "UPDATE ".$full_table_name." 
            SET supplier='ALLGIFTS' 
            WHERE supplier = 'MOTIVATES' AND license=1;"
        );
        $this->execute(
            "UPDATE ".$full_table_name." 
            SET supplier='MOTIVATES' 
            WHERE supplier = 'Lifestyle' AND license=1;"
        );
        $this->execute(
            "UPDATE ".$full_table_name." 
            SET supplier='DIGGERCARD' 
            WHERE supplier = 'NGC' AND license=1;"
        );
        $this->execute(
            "UPDATE ".$full_table_name." 
            SET supplier='CINEMASOCIETY' 
            WHERE supplier = 'CinemaSociety' AND license=1;"
        );

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