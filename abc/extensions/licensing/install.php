<?php
/**
 * @var AController $this
 */

use abc\core\ABC;
use abc\core\engine\AController;
use abc\core\lib\AAttribute_Manager;

$dbPrefix = ABC::env('DATABASES')[ABC::env('DB_CURRENT_DRIVER')]['DB_PREFIX'];
$dbName = ABC::env('DATABASES')[ABC::env('DB_CURRENT_DRIVER')]['DB_NAME'];

$sql = "DROP TABLE IF EXISTS `".$dbPrefix."licenses`;";
$result = $this->db->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS `".$this->db->table_name('licenses')."` (
    `license_id` int(11) NOT NULL AUTO_INCREMENT,
    `product_id` int(11) NOT NULL,
    `product_option_value_id` int(11) NOT NULL,
    `status` int(1) NOT NULL,
    `order_id` int(11) NOT NULL,
    `order_product_id` int(11) NOT NULL,
    `license_key` varchar(255),
    `url` text default '',
    `settings` text default '',
    `date_added` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
    `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`license_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
$result = $this->db->query($sql);

$sql = "ALTER TABLE `".$this->db->table_name('licenses')."` 
        ADD UNIQUE INDEX `".$dbPrefix."licenses_idx` 
        (`product_id` ASC, `product_option_value_id` ASC, `license_key` ASC, `order_id` ASC);";
$result = $this->db->query($sql);

//check if columns exists before adding
$sql = "SELECT *
        FROM information_schema.COLUMNS
        WHERE
        TABLE_SCHEMA = '".$dbName."'
        AND TABLE_NAME = '".$this->db->table_name('products')."'
        AND COLUMN_NAME = 'license'";
$result = $this->db->query($sql);
if (!$result->num_rows) {
    $this->db->query("ALTER TABLE ".$this->db->table_name('products')." ADD COLUMN `license` int(1) DEFAULT 0;");
}

//check if columns exists before adding
$sql = "SELECT *
        FROM information_schema.COLUMNS
        WHERE
        TABLE_SCHEMA = '".$dbName."'
        AND TABLE_NAME = '".$this->db->table_name('products')."'
        AND COLUMN_NAME = 'settings'";
$result = $this->db->query($sql);
if (!$result->num_rows) {
    $this->db->query(
        "ALTER TABLE ".$this->db->table_name('products')." 
        ADD COLUMN `settings` LONGTEXT COLLATE utf8_general_ci;");
}

//check global attribute License Validity Period
/**
 * @var $attr AAttribute_Manager
 */
$attr = ABC::getObjectByAlias('AAttribute_Manager', ['product_option']);
$global_attribute_id = $attr->addAttribute(
    array(
        'attribute_type_id'   => 1,
        'status'              => 1,
        'name'                => 'Device Count / License Validity Period',
        'attribute_parent_id' => 0,
        'element_type'        => 'R',
    )
);

$attr->addAttribute(
    array(
        'attribute_type_id'   => 1,
        'status'              => 1,
        'name'                => 'Device Count',
        'attribute_parent_id' => $global_attribute_id,
        'element_type'        => 'R',
    )
);
$attr->addAttribute(
    array(
        'attribute_type_id'   => 1,
        'status'              => 1,
        'name'                => 'Validity',
        'attribute_parent_id' => $global_attribute_id,
        'element_type'        => 'R',
    )
);

