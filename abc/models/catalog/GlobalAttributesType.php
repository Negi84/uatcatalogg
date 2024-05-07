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
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class GlobalAttributesType
 *
 * @property int $attribute_type_id
 * @property string $type_key
 * @property string $controller
 * @property int $sort_order
 * @property int $status
 *
 * @property GlobalAttributesTypeDescription $description
 * @property GlobalAttributesTypeDescription $descriptions
 *
 * @package abc\models
 */
class GlobalAttributesType extends BaseModel
{
    protected $cascadeDeletes = ['descriptions'];
    protected $primaryKey = 'attribute_type_id';
    public $timestamps = false;

    protected $casts = [
        'sort_order' => 'int',
        'status'     => 'int',
    ];

    protected $fillable = [
        'type_key',
        'controller',
        'sort_order',
        'status',
    ];

    protected $rules = [
        /** @see validate() */
        'sort_order' => [
            'checks' => [
                'integer',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => 'Sort Order is not Integer!'],
                'min' => ['default_text' => 'Sort Order value must be greater than zero'],
                'max' => ['default_text' => 'Sort Order must be less than 2147483647']
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
                'max' => ['default_text' => 'Status must be less than 2147483647']
            ],
        ]
    ];
    /**
     * @return HasOne
     */
    public function description()
    {
        return $this->hasOne(GlobalAttributesTypeDescription::class, 'attribute_type_id', 'attribute_type_id')
            ->where('language_id', '=', static::$current_language_id);
    }
    /**
     * @return HasMany
     */
    public function descriptions()
    {
        return $this->hasMany(GlobalAttributesTypeDescription::class, 'attribute_type_id');
    }
}
