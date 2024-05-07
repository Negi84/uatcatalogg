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
use abc\models\locale\Language;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class FieldDescription
 *
 * @property int $field_id
 * @property string $name
 * @property string $description
 * @property int $language_id
 * @property string $error_text
 *
 * @property Field $field
 * @property Language $language
 *
 * @package abc\models
 */
class FieldDescription extends BaseModel
{
    protected $primaryKey = 'id';
    protected $primaryKeySet = [
        'field_id',
        'language_id',
    ];

    protected $casts = [
        'field_id'    => 'int',
        'language_id' => 'int',
    ];

    protected $fillable = [
        'field_id',
        'language_id',
        'name',
        'description',
        'error_text',
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
                'integer' => ['default_text' => ':attribute is not integer!'],
                'max' => ['default_text' => ':attribute must be less than 2147483647'],
                'min' => ['default_text' => ':attribute value must be greater than zero'],
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
                'exists' => ['default_text' => ':attribute not exists in languages table'],
                'required' => ['default_text' => ':attribute required']
            ],
        ],
    ];

    public function field()
    {
        return $this->belongsTo(Field::class, 'field_id');
    }

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id');
    }
}
