<?php

if (!class_exists('\abc\core\extension\ExtensionLicensing')) {
    require_once __DIR__.DS.'core'.DS.'licensing_hooks.php';
}

$controllers = array(
    'storefront' => array(
        'responses/extension/licensing',
    ),
    'admin'      => array(
        'pages/catalog/licensing',
        'responses/listing_grid/licensing',
    ),
);

$models = array(
    'storefront' => array(
        'extension/licensing',
    ),
    'admin'      => array(
        'catalog/licensing',
    ),
);

$templates = array(
    'storefront' => array(
        'pages/catalog/licensing.tpl',
        'responses/extension/license_pdf.tpl',
    ),
    'admin'      => array(
        'pages/catalog/license_list.tpl',
        'common/type_popover.tpl',
    ),
);

$languages = array(
    'storefront' => array(
        'english/licensing/licensing',
    ),
    'admin'      => array(
        'english/licensing/licensing',
    ),
);

