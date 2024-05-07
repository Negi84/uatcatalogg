<?php
/**
* AbanteCart auto-generated migration file
*/

use abc\core\lib\AMenu;
use Phinx\Migration\AbstractMigration;

class ImportExportMenuAdd extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {
        try {
            $this->adapter->beginTransaction();

            $menu = new AMenu ( "admin" );
            if(!$menu->getMenuItem('import_export')) {
                $menu->insertMenuItem([
                        "item_id"         => "import_export",
                        "parent_id"       => "data",
                        "item_text"       => "text_import_export",
                        "item_url"        => "tool/import_export",
                        "item_icon_rl_id" => 272,
                        "item_type"       => "core",
                        "sort_order"      => "4",
                    ]
                );
            }
            $this->adapter->commitTransaction();
        } catch (\PDOException $e) {
            $this->adapter->rollbackTransaction();
            throw new Exception("Unable to delete menu item" . $e->getMessage());
        }
    }

    public function down()
    {

    }
}