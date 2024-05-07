<?php
/**
* AbanteCart auto-generated migration file
*/


use Phinx\Migration\AbstractMigration;

class BidfoodNewCustomerCommunicationFields extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {
        $fields = [
            'status' => ['string', ['limit' => 32, 'default' => '']],
            'customer_firstname' => ['string', ['limit' => 32, 'default' => '']],
            'customer_lastname' => ['string', ['limit' => 32, 'default' => '']],
            'date_sent' => ['datetime', ['default' => null]],
            'date_opened' => ['datetime', ['default' => null]],
        ];
        $table = $this->table('customer_communications');
        $tableLg = $this->table('customer_communications_chnglog');
        foreach ($fields as $fld => $definition) {
            if (!$table->hasColumn($fld)) {
                $table->addColumn($fld, $definition[0], $definition[1])->update();
            }
            if ($tableLg->exists()) {
                if (!$tableLg->hasColumn($fld)) {
                    $tableLg->addColumn($fld, $definition[0], $definition[1])->update();
                }
            }
        }
    }

    public function down()
    {
    }
}
