<?php
/**
 * AbanteCart auto-generated migration file
 */

use abc\models\catalog\Category;
use Phinx\Migration\AbstractMigration;

class AddCategoryProductsCountColumns extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {
        $table = $this->table('categories');
        if ($table->exists() && !$table->hasColumn('active_products_count')&& !$table->hasColumn('total_products_count')) {
            $table
                ->addColumn(
                    'active_products_count',
                    'integer',
                    [
                        'after'   => 'path',
                        'default' => '0',
                        'null'    => false,
                    ]
                )->addColumn(
                    'total_products_count',
                    'integer',
                    [
                        'after'   => 'path',
                        'default' => '0',
                        'null'    => false,
                    ]
                )->save();
        }
        $rows = $this->fetchAll('SELECT * FROM tims_categories');
        foreach ($rows as $row) {

            /** @var Category $category */
            $category = Category::find($row['category_id']);
            if ($category) {
                $category->touch();
            }
        }
    }

    public function down()
    {

    }
}