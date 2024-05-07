<?php
/**
* AbanteCart auto-generated migration file
*/


use Phinx\Migration\AbstractMigration;

class ChangeDateDeletedColumn extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {
        $this->execute("SET SQL_MODE = '';");

        $prefix = $this->getAdapter()->getOption('table_prefix');

        $db_name = $this->adapter->getOption('name');
        $rows = $this->fetchAll(
            "SELECT TABLE_NAME, COLUMN_NAME 
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA='".$db_name."' 
               AND TABLE_NAME LIKE '".$prefix."%' 
               AND COLUMN_NAME IN ('date_deleted');"
        );
        foreach($rows as $row){
            $update = "
                ALTER TABLE {$row['TABLE_NAME']}
                MODIFY COLUMN `".$row['COLUMN_NAME']."` timestamp NULL DEFAULT NULL ";
            $this->execute($update);

            $update = "UPDATE ".$row['TABLE_NAME']." 
            SET `".$row['COLUMN_NAME']."` = NULL 
            WHERE `".$row['COLUMN_NAME']."` = '0000-00-00 00:00:00';";
            $this->execute($update);
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
