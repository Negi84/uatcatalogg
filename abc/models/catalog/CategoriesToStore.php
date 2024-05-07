<?php
/**
 * AbanteCart, Ideal Open Source Ecommerce Solution
 * https://www.abantecart.com
 *
 * Copyright (c) 2011-2023  Belavier Commerce LLC
 *
 * This source file is subject to Open Software License (OSL 3.0)
 * License details is bundled with this package in the file LICENSE.txt.
 * It is also available at this URL:
 * <https://www.opensource.org/licenses/OSL-3.0>
 *
 * UPGRADE NOTE:
 * Do not edit or add to this file if you wish to upgrade AbanteCart to newer
 * versions in the future. If you wish to customize AbanteCart for your
 * needs please refer to https://www.abantecart.com for more information.
 */

namespace abc\models\catalog;

use abc\models\BaseModel;
use abc\models\system\Store;

/**
 * Class CategoriesToStore
 *
 * @property int $category_id
 * @property int $store_id
 *
 * @property Category $category
 * @property Store $store
 *
 * @package abc\models
 */
class CategoriesToStore extends BaseModel
{

    /**
     * @var string
     */
    protected $primaryKey = 'id';
    protected $primaryKeySet = [
        'category_id',
        'store_id'
    ];

    public $timestamps = false;

    protected $casts = [
        'category_id' => 'int',
        'store_id'    => 'int',
    ];

    /** @var array */
    protected $fillable = [
        'category_id',
        'store_id'
    ];

    protected $rules = [
        /** @see validate() */
        'category_id' => [
            'checks' => [
                'integer',
                'sometimes',
                'required',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => 'Category ID is not integer!'],
                'max' => ['default_text' => 'Category ID must be less than 2147483647'],
                'min' => ['default_text' => 'Category ID value must be greater than zero'],
                'required' => ['default_text' => 'Category ID required']
            ],
        ],
        'store_id' => [
            'checks' => [
                'integer',
                'sometimes',
                'required',
                'max:2147483647',
                'min:0',
            ],
            'messages' => [
                'integer' => ['default_text' => 'Store ID is not integer!'],
                'max' => ['default_text' => 'Store ID must be less than 2147483647'],
                'min' => ['default_text' => 'Store ID value must be greater than zero'],
                'required' => ['default_text' => 'Store ID required']
            ],
        ],
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }
}
