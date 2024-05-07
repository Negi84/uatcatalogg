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
namespace abc\models\system;

use abc\models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Job
 *
 * @property int $job_id
 * @property string $job_name
 * @property int $status
 * @property string $configuration
 * @property Carbon $start_time
 * @property Carbon $last_time_run
 * @property int $last_result
 * @property int $actor_type
 * @property int $actor_id
 * @property string $actor_name
 * @property Carbon $date_added
 * @property Carbon $date_modified
 *
 * @package abc\models
 */
class Job extends BaseModel
{
    use SoftDeletes;

    protected $primaryKey = 'job_id';
    public $timestamps = false;

    protected $casts = [
        'status'        => 'int',
        'last_result'   => 'int',
        'actor_type'    => 'int',
        'actor_id'      => 'int',
        'start_time'    => 'datetime',
        'last_time_run' => 'datetime',
        'date_added'    => 'datetime',
        'date_modified' => 'datetime'
    ];

    protected $fillable = [
        'status',
        'configuration',
        'start_time',
        'last_time_run',
        'last_result',
        'actor_type',
        'actor_id',
        'actor_name',
        'date_added',
        'date_modified',
    ];

    protected $rules = [
        /** @see validate() */
        'status' => [
            'checks' => [
                'integer',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => 'Status is not integer!'],
                'max' => ['default_text' => 'Status must be less than 2147483647'],
                'min' => ['default_text' => 'Status value must be greater than zero'],
            ],
        ],
        'last_result' => [
            'checks' => [
                'integer',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => 'Last Result is not integer!'],
                'max' => ['default_text' => 'Last Result must be less than 2147483647'],
                'min' => ['default_text' => 'Last Result value must be greater than zero'],
            ],
        ],
        'actor_type' => [
            'checks' => [
                'integer',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => 'Actor Type is not integer!'],
                'max' => ['default_text' => 'Actor Type must be less than 2147483647'],
                'min' => ['default_text' => 'Actor Type value must be greater than zero'],
            ],
        ],
        'actor_id' => [
            'checks' => [
                'integer',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => 'Actor ID is not integer!'],
                'max' => ['default_text' => 'Actor ID must be less than 2147483647'],
                'min' => ['default_text' => 'Actor ID value must be greater than zero'],
            ],
        ],
    ];
}
