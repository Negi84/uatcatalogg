<?php
/**
* AbanteCart auto-generated migration file
*/

use abc\core\lib\AMenu;
use abc\core\lib\AResourceManager;
use Phinx\Migration\AbstractMigration;

class AddGlobalAttributesGroupAndObjectTypesMenu extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {
        $prefix = $this->getAdapter()->getOption('table_prefix');
        $full_table_names = [$prefix."resource_library", $prefix."resource_descriptions"];

        foreach ($full_table_names as $full_table_name) {
            $this->execute("DROP TRIGGER IF EXISTS tr_{$full_table_name}_insert;");
            $this->execute("DROP TRIGGER IF EXISTS tr_{$full_table_name}_update;");
            $this->execute("DROP TRIGGER IF EXISTS tr_{$full_table_name}_delete;");
        }

        // add new menu item
        $rm = new AResourceManager();
        $rm->setType( 'image' );

        $language_id = 1;
        $data = [];
        $data['resource_code'] = '<i class="fa fa-project-diagram"></i>&nbsp;';
        $data['name'] = [ $language_id => 'Object types' ];
        $data['title'] = [ $language_id => '' ];
        $data['description'] = [ $language_id => '' ];
        $resource_id = $rm->addResource( $data );

        $menu = new AMenu ( "admin" );
        $menu->insertMenuItem( [
                "item_id"         => "object_types",
                "parent_id"       => "catalog",
                "item_text"       => "object_types_menu",
                "item_url"        => "catalog/object_type",
                "item_icon_rl_id" => $resource_id,
                "item_type"       => "extension",
                "sort_order"      => "20",
            ]
        );
        // add new menu item
        $rm = new AResourceManager();
        $rm->setType( 'image' );

        $data = [];
        $data['resource_code'] = '<i class="fa fa-object-group"></i>&nbsp;';
        $data['name'] = [ $language_id => 'Global Attribute Groups' ];
        $data['title'] = [ $language_id => '' ];
        $data['description'] = [ $language_id => '' ];
        $resource_id = $rm->addResource( $data );

        $menu = new AMenu ( "admin" );
        $menu->insertMenuItem( [
                "item_id"         => "global_attribute_grpups",
                "parent_id"       => "catalog",
                "item_text"       => "global_attribute_grpups",
                "item_url"        => "catalog/attribute_groups",
                "item_icon_rl_id" => $resource_id,
                "item_type"       => "extension",
                "sort_order"      => "10",
            ]
        );
    }

    public function down()
    {
        $menu = new AMenu ( "admin" );
        $menu->deleteMenuItem( "object_types" );
        $menu->deleteMenuItem( "global_attribute_grpups" );

    }
}
