<?php
/**
* AbanteCart auto-generated migration file
*/


use Phinx\Migration\AbstractMigration;

class TimsLicenseTablesNewColumns extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {
        $this->execute("SET SQL_MODE = '';");
        // create the table
        $table = $this->table('licenses');

        if(!$table->hasColumn('option_name')) {
            $prefix = $this->getAdapter()->getOption('table_prefix');
            $this->execute(
                "UPDATE ".$prefix."licenses 
                    SET `expiry_date` = NULL 
                    WHERE `expiry_date` = '0000-00-00 00:00:00';"
            );

            $table->addColumn( 'option_name', 'string', ['limit' => 255, 'default' => '', 'after' => 'product_option_value_id'] )
                  ->update();
            $table->save();
        }
    }

    public function down()
    {
    }
}