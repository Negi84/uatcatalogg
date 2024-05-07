<?php
use abc\core\ABC;
$verBuilt = ABC::env('VERSION_BUILT');
$buildId = ABC::env('BUILD_ID') ? ' Build: ' . ABC::env('BUILD_ID') : '';
ABC::env('VERSION_BUILT', $verBuilt . '/1.0.0' . $buildId . ' Stage: ' . ABC::$stage_name, true);

return [
    'sites'                 => include __DIR__.DS.'sites.php',
    'product'               => include __DIR__.DS.'product.php',
    'remove_product_fields' => [
        'shipping',
        'free_shipping',
        'ship_individually',
        'shipping_price',
    ],
];