<?php

use abc\extensions\tims_catalog\modules\workers\CategoryExport;
use abc\extensions\tims_catalog\modules\workers\ManufacturerExport;
use abc\extensions\tims_catalog\modules\workers\ProductExport;
use abc\extensions\tims_catalog\modules\workers\ProductTypeSyncBidfoodToCatalog;
use abc\extensions\tims_catalog\modules\workers\SetUuid;

return [
    'productExport'                   => ProductExport::class,
    'categoryExport'                  => CategoryExport::class,
    'manufacturerExport'              => ManufacturerExport::class,
    'setUuid'                         => SetUuid::class,
    'ProductTypeSyncBidfoodToCatalog' => ProductTypeSyncBidfoodToCatalog::class,
];
