<?php
/**
* AbanteCart auto-generated migration file
*/


use Phinx\Migration\AbstractMigration;

class AddExpiryDate extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */
    public function up()
    {

        $this->execute("SET SQL_MODE = '';");
        $table = $this->table('licenses');
        if ($table->exists()) {
            if (!$table->hasColumn('expiry_date')) {
                $table->addColumn(
                    'expiry_date',
                    'timestamp',
                    [
                        'null' => true,
                        'after'=> 'license_key'
                    ]
                )->update();
                $table->save();
            }
        }

        $translations = [
            'licensing_column_expiry_date' => 'Expiry Date',
        ];

        $this->execute(
                    "DELETE FROM tims_language_definitions 
                     WHERE `language_key` IN ('".implode("', '", array_keys($translations))."')
                         AND `block` = 'licensing_licensing'
                         AND section=1"
        );
        $table = $this->table('language_definitions');
        foreach ($translations as $key => $translation) {
                $table->insert([
                    [
                        'language_id'    => 1,
                        'section'        => 1,
                        'block'          => 'licensing_licensing',
                        'language_key'   => $key,
                        'language_value' => $translation,
                    ],
                ])
                ->save();
        }
    }

    public function down()
    {

    }
}