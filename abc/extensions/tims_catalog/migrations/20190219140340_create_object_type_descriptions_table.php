<?php
/**
* AbanteCart auto-generated migration file
*/


use Phinx\Migration\AbstractMigration;

class CreateObjectTypeDescriptionsTable extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {
        $table = $this->table('object_type_descriptions');
        if(!$table->exists()) {
            $table->addColumn( 'object_type_id', 'integer' )
                ->addColumn('language_id', 'integer')
                ->addColumn('name', 'string')
                ->addColumn('description', 'text', ['null' => true])
                ->addColumn( 'date_added', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addColumn( 'date_modified', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
                ->addColumn( 'date_deleted', 'timestamp', ['null' => true])
                ->addColumn('stage_id', 'integer', ['null' => true])
                ->addIndex(['object_type_id', 'language_id'])
                ->addForeignKey('object_type_id', 'object_types', 'object_type_id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                ->save();
        }
    }

    public function down()
    {

    }
}
