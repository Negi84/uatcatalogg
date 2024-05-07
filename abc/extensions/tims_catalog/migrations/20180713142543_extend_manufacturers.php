<?php

use Phinx\Migration\AbstractMigration;

class ExtendManufacturers extends AbstractMigration
{

    public function up()
    {
        try {
            $this->changeManufacturers();

        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    protected function changeManufacturers()
    {
        foreach (['manufacturers'] as $t) {
            $table = $this->table($t);

            if (!$table->hasColumn('contact')) {
                $table->addColumn(
                    'contact',
                    'string',
                    [
                        'after' => 'name',
                    ]
                )->update();
            }

            if (!$table->hasColumn('code')) {
                $table->addColumn(
                    'code',
                    'string',
                    [
                        'after' => 'contact',
                    ]
                )->update();
            }

            if (!$table->hasColumn('address')) {
                $table->addColumn(
                    'address',
                    'string',
                    [
                        'after' => 'code',
                    ]
                )->update();
            }

            if (!$table->hasColumn('date_added')) {
                $table->addColumn(
                    'date_added', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'after' => 'sort_order']
                )->update();
            }

            if (!$table->hasColumn('date_modified')) {
                $table->addColumn(
                    'date_modified', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'after' => 'date_added']
                )->update();
            }
        }
    }

    public function down()
    {

    }
}