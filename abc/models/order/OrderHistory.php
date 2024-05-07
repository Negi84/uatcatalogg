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

namespace abc\models\order;

use abc\models\BaseModel;
use Carbon\Carbon;

/**
 * Class OrderHistory
 *
 * @property int $order_history_id
 * @property int $order_id
 * @property int $order_status_id
 * @property int $notify
 * @property string $comment
 * @property Carbon $date_added
 * @property Carbon $date_modified
 *
 * @property OrderStatus $order_status
 *
 * @package abc\models
 */
class OrderHistory extends BaseModel
{
    protected $table = 'order_history';
    protected $primaryKey = 'order_history_id';
    protected $mainClassName = Order::class;
    protected $mainClassKey = 'order_id';

    protected $casts = [
        'order_id'        => 'int',
        'order_status_id' => 'int',
        'notify'          => 'int',
        'date_added'      => 'datetime',
        'date_modified'   => 'datetime'
    ];

    protected $fillable = [
        'order_id',
        'order_status_id',
        'notify',
        'comment',
    ];

    protected $rules = [
        /** @see validate() */
        'order_id' => [
            'checks' => [
                'integer',
                'required',
                'exists:orders',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => ':attribute is not integer!'],
                'max' => ['default_text' => ':attribute must be less than 2147483647'],
                'min' => ['default_text' => ':attribute value must be greater than zero'],
                'exists' => ['default_text' => ':attribute not exists in orders table'],
                'required' => ['default_text' => ':attribute required']
            ],
        ],
        'order_status_id' => [
            'checks' => [
                'integer',
                'required',
                'exists:order_statuses',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => ':attribute is not integer'],
                'max' => ['default_text' => ':attribute must be less than 2147483647'],
                'min' => ['default_text' => ':attribute value must be greater than zero'],
                'exists' => ['default_text' => ':attribute not exists in order_statuses table'],
                'required' => ['default_text' => ':attribute required']
            ],
        ],
        'notify' => [
            'checks' => [
                'boolean',
            ],
            'messages' => [
                '*' => [
                    'default_text' => ':attribute must be an integer!',
                ],
            ],
        ],
        'comment' => [
            'checks' => [
                'string',
                'max:1500',
            ],
            'messages' => [
                '*' => [
                    'default_text' => ':attribute must be string :max characters length!',
                ],
            ],
        ],
    ];

    public function SetCommentAttribute($value)
    {
        $this->attributes['comment'] = is_string($value) ?  strip_tags($value) : $value;
    }

    public function order_status()
    {
        return $this->belongsTo(OrderStatus::class, 'order_status_id');
    }

    public function order_status_description()
    {
        return $this->hasOne(OrderStatusDescription::class, 'order_status_id', 'order_status_id')
                    ->where('language_id', '=', static::$current_language_id);
    }

    public function save(array $options = [])
    {
        parent::save($options);
        //touch orders table
        $order = Order::find($this->order_id);
        $order->update(['order_status_id' => $this->order_status_id]);
    }
}
