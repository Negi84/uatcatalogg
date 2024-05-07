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
use abc\models\locale\Language;

/**
 * Class GlobalAttributesDescription
 *
 * @property int $attribute_id
 * @property int $language_id
 * @property string $name
 * @property string $placeholder
 * @property string $error_text
 *
 * @property GlobalAttribute $global_attribute
 * @property Language $language
 *
 * @package abc\models
 */
class GlobalAttributesDescription extends BaseModel
{
    protected $primaryKey = 'id';
    protected $primaryKeySet = [
        'attribute_id',
        'language_id',
    ];

    protected $casts = [
        'attribute_id' => 'int',
        'language_id'  => 'int',
    ];

    protected $fillable = [
        'name',
        'placeholder',
        'error_text',
    ];

    protected $rules = [
        /** @see validate() */
        'language_id' => [
            'checks' => [
                'integer',
                'exists:languages',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => 'Language ID is not Integer!'],
                'exists' => ['default_text' => 'Language ID does not exists in the languages table!'],
                'min' => ['default_text' => 'Language ID value must be greater than zero'],
                'max' => ['default_text' => 'Language ID must be less than 2147483647']
            ],
        ],
        'attribute_id' => [
            'checks' => [
                'integer',
                'exists:global_attributes',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => 'Attribute ID is not Integer!'],
                'min'     => ['default_text' => 'Attribute ID value must be greater than zero'],
                'max'     => ['default_text' => 'Attribute ID must be less than 2147483647']
            ],
        ]
    ];

    public function attribute()
    {
        return $this->belongsTo(GlobalAttribute::class, 'attribute_id');
    }

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id');
    }
}
