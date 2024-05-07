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
use abc\models\casts\Json;
use abc\models\casts\Serialized;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class TaskStep
 *
 * @property int $step_id
 * @property int $task_id
 * @property int $sort_order
 * @property int $status
 * @property Carbon $last_time_run
 * @property int $last_result
 * @property int $max_execution_time
 * @property string $controller
 * @property string $settings
 * @property Carbon $date_added
 * @property Carbon $date_modified
 *
 * @package abc\models
 */
class TaskStep extends BaseModel
{
    protected $primaryKey = 'step_id';

    protected $casts = [
        'task_id' => 'int',
        'sort_order' => 'int',
        'status' => 'int',
        'last_time_run' => 'datetime',
        'last_result' => 'int',
        'max_execution_time' => 'int',
        'controller' => 'string',
        'settings' => Serialized::class,
        'date_added' => 'datetime',
        'date_modified' => 'datetime'
    ];

    protected $fillable = [
        'task_id',
        'sort_order',
        'status',
        'last_time_run',
        'last_result',
        'max_execution_time',
        'controller',
        'settings'
    ];

    protected $rules = [
        /** @see validate() */
        'task_id' => [
            'checks' => [
                'integer',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => 'Task ID is not integer!'],
                'max' => ['default_text' => 'Task ID must be less than 2147483647'],
                'min' => ['default_text' => 'Task ID value must be greater than zero'],
            ],
        ],
        'sort_order' => [
            'checks' => [
                'integer',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => 'Sort Order is not integer!'],
                'max' => ['default_text' => 'Sort Order must be less than 2147483647'],
                'min' => ['default_text' => 'Sort Order value must be greater than zero'],
            ],
        ],
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
        'max_execution_time' => [
            'checks' => [
                'integer',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => 'Max Execution Time is not integer!'],
                'max' => ['default_text' => 'Max Execution Time must be less than 2147483647'],
                'min' => ['default_text' => 'Max Execution Time value must be greater than zero'],
            ],
        ],
    ];
}
