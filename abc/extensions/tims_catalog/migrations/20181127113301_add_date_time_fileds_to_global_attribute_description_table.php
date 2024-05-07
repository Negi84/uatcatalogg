<?php
/**
 * AbanteCart auto-generated migration file
 */

use Phinx\Migration\AbstractMigration;

class AddDateTimeFiledsToGlobalAttributeDescriptionTable extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {
        $tables = [
            'global_attributes_descriptions',
            'global_attributes_groups_descriptions',
        ];

        foreach ($tables as $tableName) {
            $table = $this->table($tableName);
            if ($table->exists()) {
                if (!$table->hasColumn('date_added')) {
                    $table->addColumn('date_added', 'timestamp', ['default' => 'CURRENT_TIMESTAMP']);
                }
                if (!$table->hasColumn('date_modified')) {
                    $table->addColumn('date_modified', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP']);
                }
                if (!$table->hasColumn('date_deleted')) {
                    $table->addColumn('date_deleted', 'timestamp', ['null' => true]);
                }
                if (!$table->hasColumn('stage_id')) {
                    $table  ->addColumn('stage_id', 'integer', ['null' => true]);
                }
                $table->save();
            }
        }
        $tables = [
            'products'                             => 'product_id',
            'customers'                            => 'customer_id',
            'addresses'                            => 'address_id',
            'language_definitions'                 => 'language_definition_id',
            'categories'                           => 'category_id',
            'category_descriptions'                => 'category_id',
            'coupons'                              => 'coupon_id',
            'coupon_descriptions'                  => 'coupon_id',
            'customer_groups'                      => 'customer_group_id',
            'downloads'                            => 'download_id',
            'download_descriptions'                => 'download_id',
            'download_attribute_values'            => 'download_attribute_id',
            'extensions'                           => 'extension_id',
            'banners'                              => 'banner_id',
            'banner_descriptions'                  => 'banner_id',
            'manufacturers'                        => 'manufacturer_id',
            'orders'                               => 'order_id',
            'customer_transactions'                => 'customer_transaction_id',
            'tax_classes'                          => 'tax_class_id',
            'tax_class_descriptions'               => 'tax_class_id',
            'tax_rates'                            => 'tax_rate_id',
            'tax_rate_descriptions'                => 'tax_rate_id',
            'product_options'                      => 'product_option_id',
            'product_option_descriptions'          => 'product_option_id',
            'product_option_values'                => 'product_option_value_id',
            'product_option_value_descriptions'    => 'product_option_value_id',
            'order_products'                       => 'order_product_id',
            'order_downloads'                      => 'order_download_id',
            'order_history'                        => 'order_history_id',
            'order_options'                        => 'order_option_id',
            'order_totals'                         => 'order_total_id',
            'product_descriptions'                 => 'product_id',
            'product_discounts'                    => 'product_discount_id',
            'product_specials'                     => 'product_special_id',
            'reviews'                              => 'review_id',
            'settings'                             => 'setting_id',
            'user_groups'                          => 'user_group_id',
            'users'                                => 'user_id',
            'pages'                                => 'page_id',
            'page_descriptions'                    => 'page_id',
            'contents'                             => 'content_id',
            'content_descriptions'                 => 'content_id',
            'blocks'                               => 'block_id',
            'block_descriptions'                   => 'block_description_id',
            'layouts'                              => 'layout_id',
            'block_layouts'                        => 'instance_id',
            'forms'                                => 'form_id',
            'form_descriptions'                    => 'form_id',
            'fields'                               => 'field_id',
            'field_descriptions'                   => 'field_id',
            'field_values'                         => 'value_id',
            'resource_library'                     => 'resource_id',
            'resource_descriptions'                => 'resource_id',
            'resource_map'                         => 'resource_id',
            'global_attributes'                    => 'attribute_id',
            'global_attributes_descriptions'       => 'attribute_id',
            'global_attributes_values'             => 'attribute_value_id',
            'global_attributes_value_descriptions' => 'attribute_value_id',
            'customer_communications'              => 'communication_id',
            'customer_notes'                       => 'note_id',
        ];

        // create audit log tables
        $this->execute("SET SQL_MODE = '';");
        $tableAdapter = new Phinx\Db\Adapter\TablePrefixAdapter($this->getAdapter());
        foreach ($tables as $table => $key) {
            $full_table_name = $tableAdapter->getAdapterTableName($table);
            $table_chnglog = $table.'_chnglog';
            $tableObj = $this->table($table_chnglog);
            if ($tableObj->exists()) {
                $this->execute("DROP TABLE {$full_table_name}_chnglog;");
            }
            $this->execute("DROP TRIGGER IF EXISTS tr_{$full_table_name}_insert;");
            $this->execute("DROP TRIGGER IF EXISTS tr_{$full_table_name}_update;");
            $this->execute("DROP TRIGGER IF EXISTS tr_{$full_table_name}_delete;");
        }

    }

    public function down()
    {
        /*
         $table = $this->table('table_name_with_prefix');
         if($table->exists()) {
             $table->drop();
         }
        */

    }
}
