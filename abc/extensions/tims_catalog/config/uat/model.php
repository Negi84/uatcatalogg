<?php

use abc\extensions\tims_catalog\modules\listeners\CategoryChangeListener;
use abc\extensions\tims_catalog\modules\listeners\CategoryDeleteListener;
use abc\extensions\tims_catalog\modules\listeners\ManufacturerChangeListener;
use abc\extensions\tims_catalog\modules\listeners\ManufacturerDeleteListener;
use abc\models\casts\Serialized;
use abc\models\catalog\Category;
use abc\models\catalog\Manufacturer;
use abc\models\catalog\Product;
use abc\models\catalog\ProductDescription;
use abc\models\customer\Customer;
use abc\models\locale\Currency;
use abc\models\user\User;
use abc\modules\listeners\ModelAuditListener;
use abc\modules\listeners\ModelCategoryListener;

return [
    /** events for ORM Models
     * can be
     * eloquent.retrieved
     * eloquent.creating
     * eloquent.created
     * eloquent.updating
     * eloquent.updated
     * eloquent.saving
     * eloquent.saved
     * eloquent.deleting
     * eloquent.deleted
     * eloquent.restoring
     * eloquent.restored
     *
     * @see more info https://laravel.com/docs/5.6/eloquent#events
     */

    'EVENTS'         => [
        //listeners for model Product on "saving" event
        //'eloquent.saving: abc\models\catalog\Product' => [ ],
        //listeners for all models on "saving" event
        'eloquent.saved: abc\models\catalog\Category'       => [
            ModelCategoryListener::class,
            CategoryChangeListener::class,
        ],
        'eloquent.deleted: abc\models\catalog\Category'     => [
            CategoryDeleteListener::class,
        ],
        'eloquent.saved: abc\models\catalog\Manufacturer'   => [
            ManufacturerChangeListener::class,
        ],
        'eloquent.deleted: abc\models\catalog\Manufacturer' => [
            ManufacturerDeleteListener::class,
        ],
        //call listeners on every model event
        'eloquent.*: *'                                     => [
            //this listener firing by base model property $auditEvents
            ModelAuditListener::class,
        ],
    ],
    'MORPH_MAP'      => [
        'Currency'           => Currency::class,
        'Customer'           => Customer::class,
        'Product'            => Product::class,
        'User'               => User::class,
        'ProductDescription' => ProductDescription::class,
        'Category'           => Category::class,
    ],
    //allow to enable/disable soft-deleting for models. Default value "false"
    //see eloquent documentation for details
    'FORCE_DELETING' => [
        Product::class      => true,
        Category::class     => true,
        Manufacturer::class => true,
    ],
    'INITIALIZE'     => [
        Product::class => [
            'properties' => [
                'fillable' => [
                    'license',
                    'cost_to_business',
                    'external_url',
                    'catalog_only',
                    'display_location',
                    'uplift_id',
                    'product_type',
                    'sites',
                    'nominal_code',
                    'supplier',
                ],
                'casts' => [
                    'license'          => 'int',
                    'catalog_only'     => 'int',
                    'product_type'     => Serialized::class,
                    'uplift_id'        => Serialized::class,
                    'sites'            => Serialized::class,
                    'cost_to_business' => 'float'
                ]
            ],
        ],
    ],
];
