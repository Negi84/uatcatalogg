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

/**
 * Class DownloadAttributeValue
 *
 * @property int $download_attribute_id
 * @property int $attribute_id
 * @property int $download_id
 * @property string $attribute_value_ids
 *
 * @property Download $download
 *
 * @package abc\models
 */
class DownloadAttributeValue extends BaseModel
{
    protected $primaryKey = 'download_attribute_id';
    public $timestamps = false;

    protected $casts = [
        'attribute_id' => 'int',
        'download_id'  => 'int',
    ];

    protected $rules = [
        /** @see validate() */
        'attribute_id' => [
            'checks' => [
                'integer',
                'exists:global_attributes',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => 'Attribute ID is not Integer!'],
                'exists' => ['default_text' => 'Attribute ID value does not exists in the global_attributes table!'],
                'max'    => ['default_text' => 'Attribute ID must be less than :max']
            ],
        ],
        'download_id' => [
            'checks' => [
                'integer',
                'exists:downloads',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => 'Download ID is not Integer!'],
                'exists' => ['default_text' => 'Download ID does not exists in the downloads table!'],
                'max' => ['default_text' => 'Download ID must be less than 2147483647']
            ],
        ],
        'attribute_value_ids' => [
            'checks'   => [
                'max:1500'
            ],
            'messages' => [
                'max' => ['default_text' => ':attribute must be less than :max characters length!']
            ],
        ]
    ];

    protected $fillable = [
        'attribute_id',
        'download_id',
        'attribute_value_ids',
    ];

    public function download()
    {
        return $this->belongsTo(Download::class, 'download_id');
    }
}
