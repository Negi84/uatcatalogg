<?php
/**
* AbanteCart auto-generated migration file
*/


use Phinx\Migration\AbstractMigration;

class CreateObjectTypeAliasesTable extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {
        $table = $this->table('object_type_aliases');
        if(!$table->exists()) {
            $table->addColumn( 'object_type', 'string' )
                ->addIndex('object_type')
                ->save();

            $table->insert([
                ['object_type' => 'Product'],
                ['object_type' => 'Category']
            ])->save();
        }
    }

    public function down()
    {

    }
}
