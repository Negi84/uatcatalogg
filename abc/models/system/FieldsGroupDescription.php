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
namespace abc\models\system;

use abc\models\BaseModel;
use abc\models\locale\Language;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class FieldsGroupDescription
 *
 * @property int $group_id
 * @property string $name
 * @property string $description
 * @property int $language_id
 *
 * @property FormGroup $form_group
 * @property Language $language
 *
 * @package abc\models
 */
class FieldsGroupDescription extends BaseModel
{

    protected $primaryKey = 'id';
    protected $primaryKeySet = [
        'group_id',
        'language_id',
    ];

    public $timestamps = false;

    protected $casts = [
        'group_id'    => 'int',
        'language_id' => 'int',
    ];

    protected $fillable = [
        'name',
        'description',
    ];

    protected $rules = [
        /** @see validate() */
        'group_id' => [
            'checks' => [
                'integer',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => 'Group ID is not integer!'],
                'max' => ['default_text' => 'Group ID must be less than 2147483647'],
                'min' => ['default_text' => 'Group ID value must be greater than zero'],
            ],
        ],
        'language_id' => [
            'checks' => [
                'integer',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => 'Language ID is not integer!'],
                'max' => ['default_text' => 'Language ID must be less than 2147483647'],
                'min' => ['default_text' => 'Language ID value must be greater than zero'],
            ],
        ],
    ];

    public function form_group()
    {
        return $this->belongsTo(FormGroup::class, 'group_id');
    }

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id');
    }
}