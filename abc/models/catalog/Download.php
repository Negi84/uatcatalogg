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
use abc\models\order\OrderDownload;
use abc\models\order\OrderDownloadsHistory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class Download
 *
 * @property int $download_id
 * @property string $filename
 * @property string $mask
 * @property int $max_downloads
 * @property int $expire_days
 * @property int $sort_order
 * @property string $activate
 * @property int $activate_order_status_id
 * @property int $shared
 * @property int $status
 * @property Carbon $date_added
 * @property Carbon $date_modified
 *
 * @property Collection $download_attribute_values
 * @property DownloadDescription $description
 * @property DownloadDescription $descriptions
 * @property Collection $order_downloads
 * @property Collection $order_downloads_histories
 * @property Collection $products_to_downloads
 *
 * @package abc\models
 */
class Download extends BaseModel
{
    protected $cascadeDeletes = ['attribute_values', 'descriptions'];

    protected $primaryKey = 'download_id';

    protected $casts = [
        'max_downloads'            => 'int',
        'expire_days'              => 'int',
        'sort_order'               => 'int',
        'activate_order_status_id' => 'int',
        'shared' => 'boolean',
        'status' => 'boolean'
    ];

    protected $fillable = [
        'filename',
        'mask',
        'max_downloads',
        'expire_days',
        'sort_order',
        'activate',
        'activate_order_status_id',
        'shared',
        'status'
    ];

    protected $rules = [
        'max_downloads' => [
            'checks' => [
                'integer',
                'min:0',
                'max:2147483647'
            ], 'messages' => [
                'integer' => ['default_text' => ':value is not Integer!'],
                'min' => ['default_text' => ':value  must be greater than zero'],
                'max' => ['default_text' => ':value must be less than :max']
            ],
        ],
        'expire_days' => [
            'checks' => [
                'integer',
                'min:0',
                'max:2147483647'
            ], 'messages' => [
                'integer' => ['default_text' => ':value is not Integer!'],
                'min' => ['default_text' => ':value  must be greater than zero'],
                'max' => ['default_text' => ':value must be less than :max']
            ],
        ],
        'sort_order' => [
            'checks' => [
                'integer',
                'min:0',
                'max:2147483647'
            ], 'messages' => [
                'integer' => ['default_text' => ':value is not Integer!'],
                'min' => ['default_text' => ':value  must be greater than zero'],
                'max' => ['default_text' => ':value must be less than :max']
            ],
        ],
        'activate_order_status_id' => [
            'checks' => [
                'integer',
                'nullable',
                'exists:order_statuses,order_status_id',
                'min:0',
                'max:2147483647'
            ], 'messages' => [
                'integer' => ['default_text' => ':value is not Integer!'],
                'exists' => ['default_text' => ':value does not exists in the order_statuses table!'],
                'min' => ['default_text' => ':value  must be greater than zero'],
                'max' => ['default_text' => ':value must be less than 2147483647']
            ],
        ],
    ];

    public function attribute_values()
    {
        return $this->hasMany(DownloadAttributeValue::class, 'download_id');
    }

    public function description()
    {
        return $this->hasOne(DownloadDescription::class, 'download_id', 'download_id')
            ->where('language_id', '=', static::$current_language_id);
    }

    public function descriptions()
    {
        return $this->hasMany(DownloadDescription::class, 'download_id');
    }

    public function order_downloads()
    {
        return $this->hasMany(OrderDownload::class, 'download_id');
    }

    public function order_downloads_histories()
    {
        return $this->hasMany(OrderDownloadsHistory::class, 'download_id');
    }
}