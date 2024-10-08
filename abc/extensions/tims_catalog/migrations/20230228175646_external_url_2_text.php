<?php
/**
 * AbanteCart auto-generated migration file
 */


use Phinx\Migration\AbstractMigration;

class ExternalUrl2Text extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {
        // create the table
        $table = $this->table('products');
        $table->changeColumn('external_url', 'text', ['null' => true])->save();
    }

    public function down()
    {

    }
}