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
 */

namespace abc\models\catalog;

use abc\models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Review
 *
 * @property int $review_id
 * @property int $product_id
 * @property int $customer_id
 * @property string $author
 * @property string $text
 * @property int $rating
 * @property int $status
 * @property Carbon $date_added
 * @property Carbon $date_modified
 *
 * @property Product $product
 *
 * @package abc\models
 */
class Review extends BaseModel
{
    use SoftDeletes;

    protected $primaryKey = 'review_id';

    protected $casts = [
        'product_id'    => 'int',
        'customer_id'   => 'int',
        'rating'        => 'int',
        'status'        => 'int',
        'date_added'    => 'datetime',
        'date_modified' => 'datetime'
    ];

    protected $fillable = [
        'product_id',
        'customer_id',
        'author',
        'text',
        'rating',
        'status',
    ];
    protected $rules = [
        /** @see validate() */
        'product_id' => [
            'checks' => [
                'integer',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => 'Product ID is not Integer!'],
                'min' => ['default_text' => 'Product ID value must be greater than zero'],
                'max' => ['default_text' => 'Product ID must be less than 2147483647'],
            ],
        ],
        'rating' => [
            'checks' => [
                'integer',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => 'Rating is not Integer!'],
                'min' => ['default_text' => 'Rating value must be greater than zero'],
                'max' => ['default_text' => 'Rating must be less than 2147483647'],
            ],
        ],
        'status' => [
            'checks' => [
                'integer',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => 'Status is not Integer!'],
                'min' => ['default_text' => 'Status value must be greater than zero'],
                'max' => ['default_text' => 'Status must be less than 2147483647'],
            ],
        ],
        'customer_id' => [
            'checks' => [
                'integer',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => 'Customer ID is not Integer!'],
                'min' => ['default_text' => 'Customer ID value must be greater than zero'],
                'max' => ['default_text' => 'Customer ID must be less than 2147483647'],
            ],
        ],
    ];
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
