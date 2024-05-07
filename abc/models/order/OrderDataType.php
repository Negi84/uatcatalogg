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
use abc\models\locale\Language;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class OrderDataType
 *
 * @property int $type_id
 * @property int $language_id
 * @property string $name
 * @property Carbon $date_added
 * @property Carbon $date_modified
 *
 * @property Language $language
 * @property Collection $order_data
 *
 * @package abc\models
 */
class OrderDataType extends BaseModel
{
    protected $cascadeDeletes = ['order_data'];

    protected $primaryKey = 'type_id';
    protected $casts = [
        'language_id'   => 'int',
        'date_added'    => 'datetime',
        'date_modified' => 'datetime'
    ];

    protected $fillable = [
        'type_id',
        'language_id',
        'name',
    ];

    protected $rules = [
        /** @see validate() */
        'type_id' => [
            'checks' => [
                'integer',
                'required',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => ':attribute is not integer!'],
                'max' => ['default_text' => ':attribute must be less than 2147483647'],
                'min' => ['default_text' => ':attribute value must be greater than zero'],
                'required' => ['default_text' => ':attribute required']
            ],
        ],
        'language_id' => [
            'checks' => [
                'integer',
                'required',
                'exists:languages',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => ':attribute is not integer!'],
                'max' => ['default_text' => ':attribute must be less than 2147483647'],
                'min' => ['default_text' => ':attribute value must be greater than zero'],
                'exists' => ['default_text' => ':attribute not exists in language table']
            ],
        ],
        'name' => [
            'checks' => [
                'string',
                'max:64',
                'required',
            ],
            'messages' => [
                '*' => [
                    'default_text' => ':attribute must be string :max characters length!',
                ],
            ],
        ],
    ];

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id');
    }

    public function order_data()
    {
        return $this->HasMany(OrderDatum::class, 'type_id');
    }
}
