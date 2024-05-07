<?php
/**
* AbanteCart auto-generated migration file
*/


use Phinx\Migration\AbstractMigration;

class AddCustomerActivationDate extends AbstractMigration
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
        if(!$table->hasColumn('date_activated')) {
            $table
                ->addColumn( 'date_activated', 'timestamp', ['after' => 'last_login', 'null' => true] )
                ->save();
        }

        $table = $this->table('customers_chnglog');
        if ($table->exists()) {
            if (!$table->hasColumn('date_activated')) {
                $table
                    ->addColumn('date_activated', 'timestamp', ['after' => 'last_login', 'null' => true])
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
                        'language_key'   => 'tims_text_date_activated',
                        'language_value' => 'Activated at',
                        'date_added'     => date('Y-m-d H:i'),
                        'date_modified'  => date('Y-m-d H:i')
                    ],
                    [
                        'language_id'    => '1',
                        'section'        => '1',
                        'block'          => 'tims_tims',
                        'language_key'   => 'tims_text_date_terms_conditions_accepted',
                        'language_value' => 'Terms & Conditions Accepted at',
                        'date_added'     => date('Y-m-d H:i'),
                        'date_modified'  => date('Y-m-d H:i')
                    ]
                ]
            )->save();
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
                    'tims_text_date_terms_conditions_accepted',
                    'tims_text_date_activated'
                ])
            ->delete($full_table_name);

    }
}
