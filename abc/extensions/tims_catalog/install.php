<?php
namespace abc\controllers\admin;

use abc\core\lib\AExtensionManager;
use abc\core\lib\AMenu;
use abc\core\lib\AResourceManager;
use Illuminate\Database\Schema\Blueprint;


/**
 * @var AController $this
 */

if (!class_exists('abc\core\ABC')) {
	header('Location: static_pages/?forbidden='.basename(__FILE__));
}
// add new menu item
$rm = new AResourceManager();
$rm->setType( 'image' );

$language_id = $this->language->getContentLanguageID();
$data = array();
$data['resource_code'] = '<i class="fa fa-paragraph"></i>&nbsp;';
$data['name'] = array( $language_id => 'Menu Icon Change Log' );
$data['title'] = array( $language_id => '' );
$data['description'] = array( $language_id => '' );
$resource_id = $rm->addResource( $data );

$menu = new AMenu ( "admin" );
$menu->insertMenuItem( array(
        "item_id"         => "tims",
        "parent_id"       => "logs",
        "item_text"       => "tims_heading_title_change_log",
        "item_url"        => "extension/database_change_log",
        "item_icon_rl_id" => $resource_id,
        "item_type"       => "extension",
        "sort_order"      => "4",
    )
);


$schema = $this->db->database();
if(!$schema->hasColumn('customers','parent_id')){
    $schema->table('customers', function (Blueprint $table) {
        $table->addColumn('integer', 'parent_id')->nullable()->default(0)->after('customer_id');
        $table->index(['parent_id']);
    });

}


if($schema->hasTable('customers_chnglog') && !$schema->hasColumn('customers_chnglog','parent_id')){
    $schema->table('customers_chnglog', function (Blueprint $table) {
        $table->addColumn('integer', 'parent_id')->nullable()->default(0)->after('customer_id');
        $table->index(['parent_id']);
    });
}

// edit settings
$this->load->model('setting/setting');
//insert gift_certificates_total before total
$sort = $this->config->get('total_sort_order');
$calc = $this->config->get('total_calculation_order');
$this->model_setting_setting->editSetting(
    'total',
    [
        'total_sort_order'        => ($sort + 1),
        'total_calculation_order' => ($calc + 1),
    ]
);
$this->model_setting_setting->editSetting(
    'tims_shipping_total',
    [
        'tims_shipping_total_status'            => 1,
        'tims_shipping_total_sort_order'        => $sort,
        'tims_shipping_total_calculation_order' => $calc,
        'tims_shipping_total_total_type'        => 'tims',
    ]
);

$this->extension_manager->addDependant(
    'tims_shipping_total',
    'tims'
);
