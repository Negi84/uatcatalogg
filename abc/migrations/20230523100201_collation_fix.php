<?php
/**
 * AbanteCart auto-generated migration file
 */


use Phinx\Migration\AbstractMigration;

class CollationFix extends AbstractMigration
{
    public function up()
    {
        // create the table
        $table = $this->table('customer_communications');
        if ($table->exists()) {
            $tableAdapter = new Phinx\Db\Adapter\TablePrefixAdapter($this->getAdapter());
            $full_table_name = $tableAdapter->getAdapterTableName('customer_communications');

            $sql = "alter table " . $full_table_name . " modify `subject` varchar(255) charset utf8mb3 not null;";
            $this->execute($sql);

            $sql = "alter table " . $full_table_name . " 
                modify `body` text charset utf8mb3 not null;";
            $this->execute($sql);

            $sql = "alter table " . $full_table_name . "
                modify `sent_to_address` text charset utf8mb3 not null;";
            $this->execute($sql);

            $sql = "alter table " . $tableAdapter->getAdapterTableName('customer_notes') . " modify `note` text charset utf8mb3 not null;";
            $this->execute($sql);

            $sql = "alter table " . $tableAdapter->getAdapterTableName('email_templates') . " charset utf8mb3;";
            $this->execute($sql);
        }
    }

    public function down()
    {
    }
}