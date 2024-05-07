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
use abc\models\casts\Serialized;
use abc\models\catalog\Download;
use abc\models\QueryBuilder;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class OrderDownload
 *
 * @property int $order_download_id
 * @property int $order_id
 * @property int $order_product_id
 * @property string $name
 * @property string $filename
 * @property string $mask
 * @property int $download_id
 * @property int $status
 * @property int $remaining_count
 * @property int $percentage
 * @property Carbon $expire_date
 * @property int $sort_order
 * @property string $activate
 * @property int $activate_order_status_id
 * @property string $attributes_data
 * @property Carbon $date_added
 * @property Carbon $date_modified
 *
 * @property Download $download
 * @property Order $order
 * @property OrderProduct $order_product
 * @property Collection $order_downloads_histories
 *
 * @package abc\models
 */
class OrderDownload extends BaseModel
{
    protected $cascadeDeletes = ['history'];

    protected $primaryKey = 'order_download_id';
    protected $mainClassName = Order::class;
    protected $mainClassKey = 'order_id';

    protected $casts = [
        'order_id'                 => 'int',
        'order_product_id'         => 'int',
        'download_id'              => 'int',
        'status'                   => 'int',
        'remaining_count'          => 'int',
        'percentage'               => 'int',
        'sort_order'               => 'int',
        'activate_order_status_id' => 'int',
        'attributes_data'          => Serialized::class,
        'expire_date'              => 'datetime',
        'date_added'               => 'datetime',
        'date_modified'            => 'datetime'
    ];

    protected $fillable = [
        'order_id',
        'order_product_id',
        'name',
        'filename',
        'mask',
        'download_id',
        'status',
        'remaining_count',
        'percentage',
        'expire_date',
        'sort_order',
        'activate',
        'activate_order_status_id',
        'attributes_data',
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
                'exists' => ['default_text' => ':attribute not exists in order table'],
                'required' => ['default_text' => ':attribute required']
            ],
        ],
        'order_product_id' => [
            'checks' => [
                'integer',
                'required',
                'exists:order_products',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => ':attribute is not integer'],
                'max' => ['default_text' => ':attribute must be less than 2147483647'],
                'min' => ['default_text' => ':attribute value must be greater than zero'],
                'exists' => ['default_text' => ':attribute not exists in order_products table'],
                'required' => ['default_text' => ':attribute required']
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
        'filename' => [
            'checks' => [
                'string',
                'max:128',
                'required',
            ],
            'messages' => [
                '*' => [
                    'default_text' => ':attribute must be string :max characters length!',
                ],
            ],
        ],
        'mask' => [
            'checks' => [
                'string',
                'max:128',
                'required',
            ],
            'messages' => [
                '*' => [
                    'default_text' => ':attribute must be string :max characters length!',
                ],
            ],
        ],
        'download_id' => [
            'checks' => [
                'integer',
                'nullable',
                'exists:downloads',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => ':attribute must be an integer!'],
                'max' => ['default_text' => ':attribute must be less than 2147483647'],
                'min' => ['default_text' => ':attribute value must be greater than zero'],
                'exists' => ['default_text' => ':attribute not exists in download table']
            ],
        ],
        'status' => [
            'checks' => [
                'boolean',
            ],
            'messages' => [
                '*' => [
                    'default_text' => ':attribute must be an integer!',
                ],
            ],
        ],
        'remaining_count' => [
            'checks' => [
                'integer',
                'nullable',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => ':attribute must be an integer!'],
                'max' => ['default_text' => ':attribute must be less than 2147483647'],
                'min' => ['default_text' => ':attribute value must be greater than zero'],
            ],
        ],
        'percentage' => [
            'checks' => [
                'integer',
                'nullable',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => ':attribute must be an integer!'],
                'max' => ['default_text' => ':attribute must be less than 2147483647'],
                'min' => ['default_text' => ':attribute value must be greater than zero'],
            ],
        ],
        'expire_date' => [
            'checks' => [
                'date',
                'nullable',
            ],
            'messages' => [
                '*' => [
                    'default_text' => ':attribute must be a date!',
                ],
            ],
        ],
        'sort_order' => [
            'checks' => [
                'integer',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => ':attribute must be an integer!'],
                'max' => ['default_text' => ':attribute must be less than 2147483647'],
                'min' => ['default_text' => ':attribute value must be greater than zero'],
            ],
        ],

        'activate' => [
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
        'activate_order_status_id' => [
            'checks' => [
                'integer',
                'required',
                'exists:order_statuses,order_status_id',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => ':attribute is not integer or absent in order_statuses table!'],
                'max' => ['default_text' => ':attribute must be less than 2147483647'],
                'min' => ['default_text' => ':attribute value must be greater than zero'],
                'exists' => ['default_text' => ':attribute not exists in order_statuses table']
            ],
        ],
    ];


    public function setDownloadIdAttribute($value)
    {
        $this->attributes['download_id'] = empty($value) ? null : (int)$value;
    }
    public function setAttributesDataAttribute($value)
    {
        $this->attributes['attributes_data'] = serialize($value);
    }

    public function download()
    {
        return $this->belongsTo(Download::class, 'download_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function order_product()
    {
        return $this->belongsTo(OrderProduct::class, 'order_product_id');
    }

    public function history()
    {
        return $this->hasMany(OrderDownloadsHistory::class, 'order_download_id');
    }

    /**
     * @param int $order_id
     *
     * @return array
     * @throws Exception
     */
    public static function getOrderDownloads($order_id)
    {
        /**
         * @var QueryBuilder $query
         */
        $query = OrderDownload::select(
            [
                'order_downloads.*',
                'order_products.*',
                'order_products.name AS product_name',
            ]
        )
                              ->leftJoin(
                                  'order_products',
                                  'order_products.order_product_id',
                                  '=',
                                  'order_downloads.order_product_id'
                              )
                              ->where('order_downloads.order_id', '=', $order_id)
                              ->orderBy('order_products.order_product_id', 'ASC')
                              ->orderBy('order_downloads.sort_order', 'ASC')
                              ->orderBy('order_downloads.name', 'ASC')
                              ->get()
                              ->toArray();

        $output = [];
        foreach ($query as $row) {
            $output[$row['product_id']]['product_name'] = $row['product_name'];
            // get download_history
            $row['download_history'] = OrderDownload::where(
                [
                    'order_id'          => $order_id,
                    'order_download_id' => $row['order_download_id'],
                ]
            )->get()->toArray();

            $output[$row['product_id']]['downloads'][] = $row;
        }

        return $output;
    }
}
