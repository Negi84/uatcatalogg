<?php

use Phinx\Migration\AbstractMigration;

class RemoteLicenseProducts extends AbstractMigration
{

    public function up()
    {
        try {
            // create the table
            $table = $this->table('licenses');
            if ($table->exists()) {
                $table->changeColumn('date_added', 'timestamp', ['default' => 'CURRENT_TIMESTAMP']);
                if (!$table->hasColumn('po_number')) {
                    $table->addColumn('po_number', 'string', ['after' => 'order_id'])->update();
                }

                if (!$table->hasColumn('site_alias')) {
                    $table->addColumn('site_alias', 'string', ['after' => 'po_number'])->update();
                }
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
            echo $e->getTraceAsString();
        }
    }

    public function down()
    {
    }
}