<?php
/**
 * AbanteCart auto-generated migration file
 */


use Phinx\Migration\AbstractMigration;

class AddLicenseExternalUrl extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {
        $this->execute("SET SQL_MODE = '';");
        // create the table
        $table = $this->table('licenses');

        if (!$table->hasColumn('url')) {
            $table->addColumn('url', 'text', ['limit' => 1500, 'default' => '', 'after' => 'license_key'])
                ->addColumn('settings', 'text', ['limit' => 1500, 'default' => '', 'after' => 'url'])
                ->update();
            $table->save();
        }
        try {
            $this->execute(
                "alter table tims_licenses
            drop key tims_licenses_idx;"
            );
        } catch (Exception $e) {
        }

        $this->execute(
            "alter table tims_licenses
                add constraint tims_licenses_idx 
            unique (product_id, license_key, order_id, product_option_value_id, url);"
        );
    }

    public function down()
    {

    }
}