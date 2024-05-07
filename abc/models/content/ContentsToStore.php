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
namespace abc\models\content;

use abc\models\BaseModel;
use abc\models\system\Store;

/**
 * Class ContentsToStore
 *
 * @property int $content_id
 * @property int $store_id
 *
 * @property Content $content
 * @property Store $store
 *
 * @package abc\models
 */
class ContentsToStore extends BaseModel
{
    /**
     * @var string
     */
    protected $primaryKey = 'id';
    protected $primaryKeySet = [
        'content_id',
        'store_id'
    ];

    protected $fillable = [
        'content_id',
        'store_id'
    ];

    public $timestamps = false;

    protected $casts = [
        'content_id' => 'int',
        'store_id'   => 'int',
    ];

    protected $rules = [
        /** @see validate() */
        'content_id' => [
            'checks' => [
                'integer',
                'sometimes',
                'required',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => 'Content ID is not integer!'],
                'max' => ['default_text' => 'Content ID must be less than 2147483647'],
                'min' => ['default_text' => 'Content ID value must be greater than zero'],
                'required' => ['default_text' => 'Content ID required']
            ],
        ],
        'store_id' => [
            'checks' => [
                'integer',
                'sometimes',
                'required',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => 'Store ID is not integer!'],
                'max' => ['default_text' => 'Store ID must be less than 2147483647'],
                'min' => ['default_text' => 'Store ID value must be greater than zero'],
                'required' => ['default_text' => 'Store ID required']
            ],
        ],
    ];

    public function content()
    {
        return $this->belongsTo(Content::class, 'content_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }
}
