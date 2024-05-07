<?php
/**
* AbanteCart auto-generated migration file
*/


use Phinx\Migration\AbstractMigration;

class BedfoodChangeDateTimeFields extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {
        $this->execute("SET SQL_MODE = '';");

        $table = $this->table('customers');
        $builder = $this->getQueryBuilder();
        $prefix = $this->getAdapter()->getOption('table_prefix');

        if($table->exists()) {

            if ($table->hasColumn('last_login') && $table->hasColumn('date_deleted')) {
                $table->changeColumn('last_login',  'timestamp', ['default' => null, 'null' => true])
                        ->changeColumn('date_deleted',  'timestamp', ['default' => null, 'null' => true])
                    ->save();

                $full_table_name = $prefix."customers";
                $this->execute("DROP TRIGGER IF EXISTS tr_{$full_table_name}_insert;");
                $this->execute("DROP TRIGGER IF EXISTS tr_{$full_table_name}_update;");
                $this->execute("DROP TRIGGER IF EXISTS tr_{$full_table_name}_delete;");

                $update = "UPDATE ".$prefix."customers
                    SET `last_login` = NULL
                    WHERE `last_login` = '0000-00-00 00:00:00';";
                $this->execute($update);


                $update = "UPDATE ".$prefix."customers
                    SET `date_deleted` = NULL
                    WHERE `date_deleted` = '0000-00-00 00:00:00';";
                $this->execute($update);

            }
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
