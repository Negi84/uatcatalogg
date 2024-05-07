<?php
/**
* AbanteCart auto-generated migration file
*/

use abc\core\lib\AMenu;
use Phinx\Migration\AbstractMigration;

class ImportExportMenuDelete extends AbstractMigration
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
            $menu->deleteMenuItem('import_export');
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