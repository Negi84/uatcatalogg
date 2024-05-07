<?php
/**
* AbanteCart auto-generated migration file
*/


use Phinx\Migration\AbstractMigration;

class AddNominalCode2Products extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {
        $tables = ["products"];
        $this->execute("SET SQL_MODE = '';");
        foreach ($tables as $tableName) {
            $table = $this->table($tableName);
            if ($table->exists()) {
                if (!$table->hasColumn('nominal_code')) {
                    $table->addColumn(
                        'nominal_code',
                        'string',
                        ['after'=> 'date_modified'])
                        ->update();
                    $table->save();
                }
            }
        }
    }

    public function down()
    {
    }
}