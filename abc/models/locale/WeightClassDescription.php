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
namespace abc\models\locale;

use abc\models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class WeightClassDescription
 *
 * @property int $weight_class_id
 * @property int $language_id
 * @property string $title
 * @property string $unit
 *
 * @property WeightClass $weight_class
 * @property Language $language
 *
 * @package abc\models
 */
class WeightClassDescription extends BaseModel
{
    use SoftDeletes;

    protected $primaryKey = 'id';
    protected $primaryKeySet = [
        'weight_class_id',
        'language_id',
    ];

    public $timestamps = false;

    protected $casts = [
        'weight_class_id' => 'int',
        'language_id' => 'int',
    ];

    protected $fillable = [
        'id',
        'title',
        'unit',
    ];
    protected $rules = [
        'id' => [
            'checks' => [
                'integer',
                'required',
                'sometimes',
                'min:1',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => [
                    'language_key' => 'error_id',
                    'language_block' => 'localisation/weight_class',
                    'default_text' => 'id must be integer!',
                    'section' => 'admin'
                ],
                'required' => [
                    'language_key' => 'error_id',
                    'language_block' => 'localisation/weight_class',
                    'default_text' => 'id required!',
                    'section' => 'admin'
                ],
                'min' => [
                    'language_key' => 'error_id',
                    'language_block' => 'localisation/weight_class',
                    'default_text' => 'id must be more 1!',
                    'section' => 'admin'
                ],
                'max' => ['default_text' => 'ID must be less than 2147483647']

            ]
        ],
        'title' => [
            'checks' => [
                'string',
                'required',
                'sometimes',
                'min:2',
                'max:32'
            ],
            'messages' => [
                'min' => [
                    'language_key' => 'error_title',
                    'language_block' => 'localisation/weight_class',
                    'default_text' => 'Title must be more 2 characters',
                    'section' => 'admin'
                ],
                'max' => [
                    'language_key' => 'error_title',
                    'language_block' => 'localisation/weight_class',
                    'default_text' => 'Title must be no more than 32 characters',
                    'section' => 'admin'
                ],
                'required' => [
                    'language_key' => 'error_title',
                    'language_block' => 'localisation/weight_class',
                    'default_text' => 'Title required!',
                    'section' => 'admin'
                ],
                'string' => [
                    'language_key' => 'error_title',
                    'language_block' => 'localisation/weight_class',
                    'default_text' => 'name must be string!',
                    'section' => 'admin'
                ],
            ]
        ],
        'unit' => [
            'checks' => [
                'string',
                'required',
                'sometimes',
                'max:4'
            ],
            'messages' => [
                'max' => [
                    'language_key' => 'error_unit',
                    'language_block' => 'localisation/weight_class',
                    'default_text' => 'unit must be no more than 4 characters',
                    'section' => 'admin'
                ],
                'required' => [
                    'language_key' => 'error_unit',
                    'language_block' => 'localisation/weight_class',
                    'default_text' => 'unit required!',
                    'section' => 'admin'
                ],
                'string' => [
                    'language_key' => 'error_unit',
                    'language_block' => 'localisation/weight_class',
                    'default_text' => 'unit must be string!',
                    'section' => 'admin'
                ],

            ]
        ]
    ];

    public function weight_class()
    {
        return $this->belongsTo(WeightClass::class, 'weight_class_id');
    }

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id');
    }
}
