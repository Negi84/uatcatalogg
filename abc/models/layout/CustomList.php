<?php
/**
 * AbanteCart, Ideal Open Source Ecommerce Solution
 * http://www.abantecart.com
 *
 * Copyright 2011-2023 Belavier Commerce LLC
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
namespace abc\models\layout;

use abc\models\BaseModel;
use Carbon\Carbon;

/**
 * Class CustomList
 *
 * @property int $rowid
 * @property int $custom_block_id
 * @property string $data_type
 * @property int $id
 * @property int $sort_order
 * @property Carbon $date_added
 * @property Carbon $date_modified
 *
 * @property CustomBlock $custom_block
 *
 * @package abc\models
 */
class CustomList extends BaseModel
{
    protected $primaryKey = 'rowid';

    protected $casts = [
        'custom_block_id' => 'int',
        'data_type'       => 'string',
        'id'              => 'int',
        'store_id'        => 'int',
        'sort_order'      => 'int'
    ];

    protected $fillable = [
        'custom_block_id',
        'data_type',
        'id',
        'store_id',
        'sort_order',
    ];

    protected $rules = [
        /** @see validate() */
        'custom_block_id' => [
            'checks' => [
                'int',
                'required',
                'sometimes',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'int' => ['default_text' => 'Block ID is not integer!'],
                'max' => ['default_text' => 'Block ID must be less than 2147483647'],
                'min' => ['default_text' => 'Block ID value must be greater than zero'],
                'required' => ['default_text' => 'Block ID required']
            ]
        ],
        'data_type' => [
            'checks' => [
                'string',
                'max:70'
            ],
            'messages' => [
                '*' => ['default_text' => 'Data Type is empty or have length greater than 70 chars!'],
            ]
        ],
        'id' => [
            'checks' => [
                'int',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'int' => ['default_text' => 'List Item ID is not integer!'],
                'max' => ['default_text' => 'List Item ID must be less than 2147483647'],
                'min' => ['default_text' => 'List Item ID value must be greater than zero'],
            ]
        ],
        'store_id' => [
            'checks' => [
                'int',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'int' => ['default_text' => 'Store ID is not integer!'],
                'max' => ['default_text' => 'Store ID must be less than 2147483647'],
                'min' => ['default_text' => 'Store ID value must be greater than zero'],

            ]
        ],
        'sort_order' => [
            'checks' => [
                'int',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'int' => ['default_text' => 'Sort order is not integer!'],
                'max' => ['default_text' => 'Sort order must be less than 2147483647'],
                'min' => ['default_text' => 'Sort order value must be greater than zero'],
            ]
        ],
    ];

    public function custom_block()
    {
        return $this->belongsTo(CustomBlock::class, 'custom_block_id');
    }
}