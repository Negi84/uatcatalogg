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
namespace abc\models\system;

use abc\models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class FieldValue
 *
 * @property int $value_id
 * @property int $field_id
 * @property string $value
 * @property int $language_id
 *
 * @property Field $field
 *
 * @package abc\models
 */
class FieldValue extends BaseModel
{
    protected $primaryKey = 'value_id';
    public $timestamps = false;

    protected $casts = [
        'field_id'    => 'int',
        'language_id' => 'int',
    ];

    protected $fillable = [
        'field_id',
        'value',
        'language_id',
    ];

    protected $rules = [
        /** @see validate() */
        'field_id' => [
            'checks' => [
                'integer',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => 'Field ID is not integer!'],
                'max' => ['default_text' => 'Field ID must be less than 2147483647'],
                'min' => ['default_text' => 'Field ID value must be greater than zero'],
            ],
        ],
        'language_id' => [
            'checks' => [
                'integer',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => 'Language ID is not integer!'],
                'max' => ['default_text' => 'Language ID must be less than 2147483647'],
                'min' => ['default_text' => 'Language ID value must be greater than zero'],
            ],
        ],
    ];

    public function field()
    {
        return $this->belongsTo(Field::class, 'field_id');
    }
}
