<?php
/**
 * AbanteCart auto-generated migration file
 */

use Phinx\Migration\AbstractMigration;

class AddExternalUrlFieldtoProductTable extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {

        $table = $this->table('products');
        if ($table->exists() && !$table->hasColumn('catalog_only')) {
            $table->addColumn('catalog_only', 'boolean', ['after' => 'product_type', 'null' => true, 'default' => false])
                ->save();
        }
        if ($table->exists() && !$table->hasColumn('external_url')) {
            $table->addColumn('external_url', 'string', ['after' => 'catalog_only', 'null' => true])
                ->save();
        }
    }

    public function down()
    {
        $table = $this->table('products');
        if ($table->exists() && $table->hasColumn('catalog_only')) {
            $table->removeColumn('catalog_only')
                ->save();
        }
        if ($table->exists() && $table->hasColumn('external_url')) {
            $table->removeColumn('external_url')
                ->save();
        }
    }
}
