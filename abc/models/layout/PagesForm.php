<?php
/**
 * AbanteCart, Ideal Open Source Ecommerce Solution
 * http://www.abantecart.com
 *
 * Copyright 2011-2023 Belavier Commerce LLC
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
namespace abc\models\layout;

use abc\models\BaseModel;
use abc\models\system\Form;

/**
 * Class PagesForm
 *
 * @property int $page_id
 * @property int $form_id
 *
 * @property Form $form
 * @property Page $page
 *
 */
class PagesForm extends BaseModel
{

    protected $primaryKey = 'id';
    protected $primaryKeySet = [
        'page_id',
        'form_id'
    ];
    public $timestamps = false;

    protected $casts = [
        'page_id' => 'int',
        'form_id' => 'int',
    ];

    protected $rules = [
        /** @see validate() */
        'page_id' => [
            'checks' => [
                'int',
                'required',
                'sometimes',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'int' => ['default_text' => 'Page ID is not integer!'],
                'max' => ['default_text' => 'Page ID must be less than 2147483647'],
                'min' => ['default_text' => 'Page ID value must be greater than zero'],
                'required' => ['default_text' => 'Page ID required']
            ]
        ],
        'form_id' => [
            'checks' => [
                'int',
                'required',
                'sometimes',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'int' => ['default_text' => 'Form ID is not integer!'],
                'max' => ['default_text' => 'Form ID must be less than 2147483647'],
                'min' => ['default_text' => 'Form ID value must be greater than zero'],
                'required' => ['default_text' => 'Form ID required']
            ]
        ],
    ];

    public function form()
    {
        return $this->belongsTo(Form::class, 'form_id');
    }

    public function page()
    {
        return $this->belongsTo(Page::class, 'page_id');
    }
}