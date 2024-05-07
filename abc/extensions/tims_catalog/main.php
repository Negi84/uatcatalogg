<?php

use abc\core\ABC;

if (!class_exists('abc\core\ABC')) {
    header('Location: static_pages/?forbidden='.basename(__FILE__));
}

require_once
    ABC::env('DIR_APP_EXTENSIONS')
    .'tims_catalog'.DS
    .'core'.DS
    .'tims_catalog_hooks.php';

$controllers = [
    'storefront' => [
    ],
    'admin'      => [
        'responses/extension/tims_catalog',
        'task/extension/tims_catalog',
        'api/catalog/license_product',
        'api/catalog/purchase_order',
    ]
];

$models = [
    'admin'      => [
        'extension/tims_catalog',
    ],
    'storefront' => [],
];

$languages = [
    'storefront' => [],
    'admin'      => [
        'tims_catalog/tims_catalog'
    ],
];

$templates = [
    'storefront' => [],
    'admin'      => [
        'common/sync_button.tpl',
    ]
];
