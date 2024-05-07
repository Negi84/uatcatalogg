<?php
/**
 * AbanteCart, Ideal Open Source Ecommerce Solution
 * http://www.abantecart.com
 *
 * Copyright 2011-2022 Belavier Commerce LLC
 *
 * This source file is subject to Open Software License (OSL 3.0)
 * License details is bundled with this package in the file LICENSE.txt.
 * It is also available at this URL:
 * <http://www.opensource.org/licenses/OSL-3.0>
 *
 * UPGRADE NOTE:
 * Do not edit or add to this file if you wish to upgrade AbanteCart to newer
 * versions in the future. If you wish to customize AbanteCart for your
 * needs please refer to http://www.abantecart.com for more information.
 *
 */

namespace abc\models\catalog;

use abc\models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ProductDiscount
 *
 * @property int $product_discount_id
 * @property int $product_id
 * @property int $customer_group_id
 * @property int $quantity
 * @property int $priority
 * @property float $price
 * @property Carbon|string $date_start
 * @property Carbon|string $date_end
 *
 * @property Product $product
 *
 * @package abc\models
 */
class ProductDiscount extends BaseModel
{
    protected $primaryKey = 'product_discount_id';
    protected $mainClassName = Product::class;
    protected $mainClassKey = 'product_id';

    protected $touches = ['product'];

    protected $casts = [
        'product_id'        => 'int',
        'customer_group_id' => 'int',
        'quantity'          => 'int',
        'priority'          => 'int',
        'price'             => 'float',
        'date_start'        => 'datetime',
        'date_end'          => 'datetime',
        'date_added'        => 'datetime',
        'date_modified'     => 'datetime'
    ];

    protected $fillable = [
        'product_id',
        'customer_group_id',
        'quantity',
        'priority',
        'price',
        'date_start',
        'date_end',
    ];

    protected $rules = [
        /** @see validate() */
        'product_id' => [
            'checks' => [
                'integer',
                'sometimes',
                'required',
                'exists:products',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => 'Product ID is not Integer!'],
                'exists' => ['default_text' => 'Product ID absent in products table!'],
                'max' => ['default_text' => 'Product ID must be less than 2147483647'],
            ],
        ],

        'customer_group_id' => [
            'checks' => [
                'integer',
                'sometimes',
                'required',
                'min:0',
                'exists:customer_groups',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => 'Customer Group ID must be an integer '],
                'exists' => ['default_text' => 'Customer Group ID not presents in the customer_groups table!'],
                'max' => ['default_text' => 'Customer Group ID must be less than 2147483647'],
                'min' => ['default_text' => 'Customer Group ID value must be greater than zero'],
                'required' => ['default_text' => 'Customer Group ID required']
            ],
        ],

        'quantity' => [
            'checks' => [
                'integer',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => 'Product Discount Quantity must be an integer!'],
                'min' => ['default_text' => 'Product Discount Quantity must be greater than zero'],
                'max' => ['default_text' => 'Product Discount Quantity must be less than 2147483647'],
            ],
        ],

        'priority' => [
            'checks' => [
                'integer',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => 'Priority must be an integer!'],
                'min' => ['default_text' => 'Priority must be greater than zero'],
                'max' => ['default_text' => 'Priority must be less than 2147483647'],
            ],
        ],

        'price' => [
            'checks' => [
                'numeric',
            ],
            'messages' => [
                '*' => [
                    'default_text' => ':attribute must be numeric!',
                ],
            ],
        ],

        'date_start' => [
            'checks' => [
                'date_format:Y-m-d H:i:s',
                'nullable',
            ],
            'messages' => [
                '*' => [
                    'default_text' => ':attribute. Wrong date format!',
                ],
            ],
        ],

        'date_end' => [
            'checks' => [
                'date_format:Y-m-d H:i:s',
                'nullable',
            ],
            'messages' => [
                '*' => [
                    'default_text' => ':attribute. Wrong date format!',
                ],
            ],
        ],
    ];

    /**
     * @return BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
