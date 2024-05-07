<?php

use abc\core\engine\Registry;
use abc\modules\events\ABaseEvent;
use Phinx\Migration\AbstractMigration;

class AddCustomerAdvancedStatus extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {
        $this->execute("SET SQL_MODE = '';");
        $table = $this->table('customers');
        if(!$table->hasColumn('advanced_status')) {
            $table
                ->addColumn( 'advanced_status', 'string', ['limit' => 128, 'after' => 'status', 'default' => ''] )
                ->save();
        }

        $table = $this->table('customers_chnglog');
        if ($table->exists()) {
            if (!$table->hasColumn('advanced_status')) {
                $table
                    ->addColumn('advanced_status', 'string', ['limit' => 128, 'after' => 'status', 'default' => ''])
                    ->save();
            }
        }

        //add language definitions
        $this->table('language_definitions')
            ->insert(
                [
                    [
                        'language_id'    => '1',
                        'section'        => '1',
                        'block'          => 'tims_tims',
                        'language_key'   => 'tims_select_advanced_status',
                        'language_value' => '--Select Account Status--',
                        'date_added'     => date('Y-m-d H:i'),
                        'date_modified'  => date('Y-m-d H:i')
                    ],[
                        'language_id'    => '1',
                        'section'        => '1',
                        'block'          => 'tims_tims',
                        'language_key'   => 'tims_consolidation_status_unknown',
                        'language_value' => 'Unknown',
                        'date_added'     => date('Y-m-d H:i'),
                        'date_modified'  => date('Y-m-d H:i')
                    ],[
                        'language_id'    => '1',
                        'section'        => '1',
                        'block'          => 'tims_tims',
                        'language_key'   => 'tims_consolidation_status_independent',
                        'language_value' => 'Independent',
                        'date_added'     => date('Y-m-d H:i'),
                        'date_modified'  => date('Y-m-d H:i')
                    ],[
                        'language_id'    => '1',
                        'section'        => '1',
                        'block'          => 'tims_tims',
                        'language_key'   => 'tims_column_advanced_status',
                        'language_value' => 'Account Status',
                        'date_added'     => date('Y-m-d H:i'),
                        'date_modified'  => date('Y-m-d H:i')
                    ],[
                        'language_id'    => '1',
                        'section'        => '1',
                        'block'          => 'tims_tims',
                        'language_key'   => 'tims_advanced_status_activated',
                        'language_value' => 'Activated',
                        'date_added'     => date('Y-m-d H:i'),
                        'date_modified'  => date('Y-m-d H:i')
                    ],[
                        'language_id'    => '1',
                        'section'        => '1',
                        'block'          => 'tims_tims',
                        'language_key'   => 'tims_advanced_status_active',
                        'language_value' => 'Active (New)',
                        'date_added'     => date('Y-m-d H:i'),
                        'date_modified'  => date('Y-m-d H:i')
                    ],[
                        'language_id'    => '1',
                        'section'        => '1',
                        'block'          => 'tims_tims',
                        'language_key'   => 'tims_advanced_status_inactive',
                        'language_value' => 'Inactive',
                        'date_added'     => date('Y-m-d H:i'),
                        'date_modified'  => date('Y-m-d H:i')
                    ],[
                        'language_id'    => '1',
                        'section'        => '1',
                        'block'          => 'tims_tims',
                        'language_key'   => 'tims_advanced_status_suspended',
                        'language_value' => 'Suspended',
                        'date_added'     => date('Y-m-d H:i'),
                        'date_modified'  => date('Y-m-d H:i')
                    ],[
                        'language_id'    => '1',
                        'section'        => '1',
                        'block'          => 'tims_tims',
                        'language_key'   => 'tims_select_advanced_status',
                        'language_value' => '--Select Account Status--',
                        'date_added'     => date('Y-m-d H:i'),
                        'date_modified'  => date('Y-m-d H:i')
                    ],
                ]
            )->save();

        //update all statuses for all customers
        $tableAdapter = new Phinx\Db\Adapter\TablePrefixAdapter($this->getAdapter());
        $full_table_name = $tableAdapter->getAdapterTableName('customers');
        $builder = $this->getQueryBuilder();
        $result = $builder->select(['customer_id' => 'customer_id'])
                    ->from($full_table_name)
                    ->execute()->fetchAll('assoc');
        //call event
        if($result) {
            foreach($result as $row) {
                H::event(
                    'abc\models\admin\customer@update',
                    [new ABaseEvent($row['customer_id'], 'editCustomer', [])]);
            }
        }
    }

    public function down()
    {
        $tableAdapter = new Phinx\Db\Adapter\TablePrefixAdapter($this->getAdapter());
        $full_table_name = $tableAdapter->getAdapterTableName('language_definitions');
        $builder = $this->getQueryBuilder();
        $builder
            ->whereInList(
                'language_key',
                [
                    'tims_column_advanced_status',
                    'tims_advanced_status_activated',
                    'tims_advanced_status_active',
                    'tims_advanced_status_inactive',
                    'tims_advanced_status_suspended',
                    'tims_select_advanced_status',
                ])
            ->delete($full_table_name);
    }
}

