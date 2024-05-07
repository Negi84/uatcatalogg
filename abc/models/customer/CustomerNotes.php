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

namespace abc\models\customer;

use abc\models\BaseModel;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method static CustomerNotes create(array $data)
 */
class CustomerNotes extends BaseModel
{
    protected $primaryKey = 'note_id';

    protected $mainClassName = Customer::class;
    protected $mainClassKey = 'customer_id';

    protected $casts = [
        'customer_id'   => 'int',
        'user_id'       => 'int',
        'stage_id'      => 'int',
        'note'          => 'string',
        'date_added'    => 'datetime',
        'date_modified' => 'datetime'
    ];

    protected $fillable = [
        'customer_id',
        'user_id',
        'note',
        'date_added',
        'date_modified',
    ];

    protected $rules = [
        /** @see validate() */
        'customer_id' => [
            'checks' => [
                'integer',
                'min:0',
                'max:2147483647',
                'exists:customers'
            ],
            'messages' => [
                'integer' => ['default_text' => 'Customer ID is not integer!'],
                'min' => ['default_text' => 'Customer ID value must be greater than zero'],
                'max' => ['default_text' => 'Customer ID must be less than 2147483647']
            ],
        ],
        'user_id' => [
            'checks' => [
                'integer',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => 'User ID is not integer!'],
                'min' => ['default_text' => 'User ID value must be greater than zero'],
                'max' => ['default_text' => 'User ID must be less than 2147483647']
            ],
        ],
        'stage_id' => [
            'checks' => [
                'integer',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => 'Stage ID is not integer!'],
                'min' => ['default_text' => 'Stage ID value must be greater than zero'],
                'max' => ['default_text' => 'Stage ID must be less than 2147483647']
            ],
        ],
    ];
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * @param int $customerId
     *
     * @return Collection|false
     */
    public static function getNotes(int $customerId)
    {
        if (!$customerId) {
            return false;
        }
        /** @var Collection $notes */
        $notes = self::select([
            'customer_notes.note',
            'customer_notes.date_added as note_added',
            'customer_notes.customer_id',
            'users.lastname',
            'users.firstname',
            'users.username',
        ])->leftJoin('users', 'customer_notes.user_id', '=', 'users.user_id')
            ->where('customer_id', '=', $customerId)
            ->orderBy('note_added')
            ->get();

        return $notes;
    }
}