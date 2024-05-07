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
 * Class DownloadDescription
 *
 * @property int $download_id
 * @property int $language_id
 * @property string $name
 *
 * @property Download $download
 * @property Language $language
 *
 * @package abc\models
 */
class DownloadDescription extends BaseModel
{
    protected $primaryKey = 'id';
    protected $primaryKeySet = [
        'download_id',
        'language_id',
    ];

    protected $casts = [
        'download_id' => 'int',
        'language_id' => 'int',
    ];

    protected $rules = [
        /** @see validate() */
        'language_id' => [
            'checks' => [
                'integer',
                'exists:languages',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => 'Language ID is not Integer!'],
                'exists' => ['default_text' => 'Language ID does not exists in the languages table!'],
                'max'    => ['default_text' => 'Language ID must be less than :max']
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
                'max'    => ['default_text' => 'Download ID must be less than :max']
            ],
        ],
        'name'        => [
            'checks'   => [
                'max:64'
            ],
            'messages' => [
                'max' => ['default_text' => 'Name must be less than :max characters length!']
            ],
        ]
    ];

    protected $fillable = [
        'name',
    ];

    public function download()
    {
        return $this->belongsTo(Download::class, 'download_id');
    }

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id');
    }
}
