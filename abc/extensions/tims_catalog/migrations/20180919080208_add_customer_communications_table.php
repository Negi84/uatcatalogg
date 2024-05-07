<?php
/**
* AbanteCart auto-generated migration file
*/


use Phinx\Migration\AbstractMigration;

class AddCustomerCommunicationsTable extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {
        $this->execute("SET SQL_MODE = '';");
        $table = $this->table('customer_communications', ['id' => 'communication_id']);
        if(!$table->exists()) {
            $table->addColumn( 'customer_id', 'integer' )
                ->addColumn( 'user_id', 'integer',['default' => 0] )
                ->addColumn( 'type', 'string', ['limit' => 128] )
                ->addColumn( 'subject', 'string')
                ->addColumn( 'body', 'text')
                ->addColumn( 'sent_to_address', 'text', ['default' => ''])
                ->addColumn( 'date_added', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
                ->addColumn( 'date_modified', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update'  => 'CURRENT_TIMESTAMP'])
                ->addForeignKey('customer_id', 'customers', 'customer_id')
                ->create();
        }

    }

    public function down()
    {

        $table = $this->table("customer_communications");
        if($table->exists()) {
            $table->drop();
            $table->save();
        }

    }
}