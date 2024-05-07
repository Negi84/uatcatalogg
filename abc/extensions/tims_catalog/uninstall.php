<?php

namespace abc\controllers\admin;

use abc\core\lib\AExtensionManager;
use abc\core\lib\AMenu;
use Illuminate\Database\Schema\Blueprint;

if ( ! class_exists( 'abc\core\ABC' ) ) {
    header( 'Location: static_pages/?forbidden='.basename( __FILE__ ) );
}

//delete menu item
$menu = new AMenu ( "admin" );
$menu->deleteMenuItem( "tims" );

/**
 * @var AExtensionManager $this
 */

$schema = $this->db->database();
if($schema->hasColumn('customers','parent_id')){
    $schema->table('customers', function (Blueprint $table) {
        $table->dropColumn('parent_id');
    });

}


if($schema->hasTable('customers_chnglog') && $schema->hasColumn('customers_chnglog','parent_id')){
    $schema->table('customers_chnglog', function (Blueprint $table) {
        $table->dropColumn('parent_id');
    });
}
