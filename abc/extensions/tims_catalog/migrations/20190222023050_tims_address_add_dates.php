<?php
/**
* AbanteCart auto-generated migration file
*/


use Phinx\Migration\AbstractMigration;

class TimsAddressAddDates extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {
        $this->execute("SET SQL_MODE = '';");

        $table = $this->table('addresses');
        if (!$table->hasColumn('date_added') ) {
            $table->addColumn( 'date_added', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])->update();
        }
        if (!$table->hasColumn('date_modified') ) {
            $table->addColumn( 'date_modified', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update'  => 'CURRENT_TIMESTAMP'])->update();
        }
        $table = $this->table('addresses_chnglog');
        if ($table->exists()) {
            if (!$table->hasColumn('date_added')) {
                $table->addColumn('date_added', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])->update();
            }
            if (!$table->hasColumn('date_modified')) {
                $table->addColumn('date_modified', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])->update();
            }
        }

        $table = $this->table('customer_transactions');
        if (!$table->hasColumn('date_expires') ) {
            $table->addColumn( 'date_expires', 'datetime', ['default' => NULL])->update();
        }
        $table = $this->table('customer_transactions_chnglog');
        if ($table->exists()) {
            if (!$table->hasColumn('date_expires')) {
                $table->addColumn('date_expires', 'datetime', ['default' => null])->update();
            }
        }

    }

    public function down()
    {
    }
}
