<?php
/**
* AbanteCart auto-generated migration file
*/

use abc\models\catalog\Category;
use Phinx\Migration\AbstractMigration;

class AddCategoryPath extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {
        $table = $this->table('categories');
        if($table->exists() && !$table->hasColumn('path')) {
            $table->addColumn( 'path', 'string', ['after' => 'parent_id', 'default' => '', 'null' => false, 'limit' => 255] )
                ->save();
        }
        $rows = $this->fetchAll('SELECT * FROM tims_categories');
        foreach($rows as $row){

                /** @var Category $category */
                $category = Category::find($row['category_id']);
                if($category){
                    $category->update(['path' => $category->getPath($row['category_id'], 'id')]);
                }
        }
    }

    public function down()
    {    }
}