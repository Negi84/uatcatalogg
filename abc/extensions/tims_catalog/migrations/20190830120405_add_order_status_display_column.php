<?php
/**
 * AbanteCart auto-generated migration file
 */

use Phinx\Migration\AbstractMigration;

class AddOrderStatusDisplayColumn extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {
        $table = $this->table('order_statuses');
        if ($table->exists()) {
            $table->addColumn('display_status', 'integer',
                ['default' => '1', 'null' => false, 'after' => 'status_text_id'])
                  ->save();
        }
    }

    public function down()
    {
        $table = $this->table('order_statuses');
        if ($table->exists() && $table->hasColumn('display_status')) {
            $table->removeColumn('display_status')
                  ->save();
        }
    }
}