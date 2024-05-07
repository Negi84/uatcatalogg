<?php

use abc\extensions\tims_catalog\modules\listeners\ProductImportListener;
use abc\extensions\tims_catalog\modules\listeners\ProductImportTaskCompleteListener;

return [

    'abc\models\admin\ModelToolImportProcess@addUpdateProduct' => [
        ProductImportListener::class
    ],
    'abc\core\lib\ATaskManager@deleteTask' => [
        ProductImportTaskCompleteListener::class
    ]
];