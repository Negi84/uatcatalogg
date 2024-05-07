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

namespace abc\models\catalog;

use abc\models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ResourceMap
 *
 * @property int $resource_id
 * @property string $object_name
 * @property int $object_id
 * @property bool $default
 * @property int $sort_order
 * @property Carbon $date_added
 * @property Carbon $date_modified
 *
 * @property ResourceLibrary $resource_library
 *
 * @package abc\models
 */
class ResourceMap extends BaseModel
{
    protected $primaryKey = 'id';
    protected $primaryKeySet = [
        'resource_id',
        'object_id',
        'object_name',
    ];

    protected $table = 'resource_map';
    protected $casts = [
        'resource_id'   => 'int',
        'object_id'     => 'int',
        'default'       => 'bool',
        'sort_order'    => 'int',
        'date_added'    => 'datetime',
        'date_modified' => 'datetime'
    ];

    protected $fillable = [
        'resource_id',
        'object_id',
        'object_name',
        'default',
        'sort_order',
        'date_added',
        'date_modified',
    ];
    protected $rules = [
        /** @see validate() */
        'resource_id' => [
            'checks' => [
                'integer',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => 'Resource ID is not Integer!'],
                'min' => ['default_text' => 'Resource ID value must be greater than zero'],
                'max' => ['default_text' => 'Resource ID must be less than 2147483647'],
            ],
        ],
        'object_id' => [
            'checks' => [
                'integer',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => 'Object ID is not Integer!'],
                'min' => ['default_text' => 'Object ID value must be greater than zero'],
                'max' => ['default_text' => 'Object ID must be less than 2147483647'],
            ],
        ],
        'sort_order' => [
            'checks' => [
                'integer',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => 'Sort Order is not Integer!'],
                'min' => ['default_text' => 'Sort Order value must be greater than zero'],
                'max' => ['default_text' => 'Sort Order must be less than 2147483647'],
            ],
        ],
    ];

    public function resource_library()
    {
        return $this->belongsTo(ResourceLibrary::class, 'resource_id');
    }
}
