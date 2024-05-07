<?php
/**
 * AbanteCart auto-generated migration file
 */


use Phinx\Migration\AbstractMigration;

class DbChangeDefaultValues extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {
        $tableAdapter = new Phinx\Db\Adapter\TablePrefixAdapter($this->getAdapter());

        $sql = "alter table tims_customer_notifications
                    alter column stage_id set default 0;
                alter table tims_customer_communications
                    modify date_sent datetime null;
                
                alter table tims_customer_communications
                    modify date_opened datetime null;
                
                alter table tims_customer_communications
                    alter column stage_id set default 0; ";
        $this->execute($sql);
    }

    public function down()
    {


    }
}