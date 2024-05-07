<?php
/**
* AbanteCart auto-generated migration file
*/


use Phinx\Migration\AbstractMigration;

class AddLocationFieldToProductTable extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {
      $table = $this->table('products');
        if($table->exists() && !$table->hasColumn('display_location')) {
            $table->addColumn( 'display_location', 'text', ['after' => 'external_url', 'null' => true] )
                ->save();
        }
    }

    public function down()
    {
        $table = $this->table('products');
        if($table->exists() && $table->hasColumn('display_location')) {
            $table->removeColumn('display_location')
                ->save();
        }

    }
}
