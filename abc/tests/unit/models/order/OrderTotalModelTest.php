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

namespace Tests\unit\models\order;

use abc\models\order\OrderTotal;
use Illuminate\Validation\ValidationException;
use Tests\unit\ATestCase;

/**
 * Class OrderTotalModelTest
 */
class OrderTotalModelTest extends ATestCase
{

    protected function setUp():void
    {
        //init
    }

    public function testValidator()
    {
        //validate
        $data = [
            'order_id'   => 'fail',
            'title'      => 0.000000000009,
            'text'       => 0.000000000009,
            'value'      => 'fail',
            'data'       => 0.000000000009,
            'sort_order' => 'fail',
            'type'       => 0.000000000009,
            'key'        => 0.000000000009,
        ];

        $orderStatus = new OrderTotal();
        $errors = [];
        try {
            $orderStatus->validate($data);
        } catch (ValidationException $e) {
            $errors = $orderStatus->errors()['validation'];
        }
        $this->assertCount(7, $errors);

        //validate
        $data = [
            'order_id'   => 2,
            'title'      => 'Test Total:',
            'text'       => '$0.01',
            'value'      => 0.01,
            'data'       => ['some-data' => 'some_value'],
            'sort_order' => 1,
            'type'       => 'unittest',
            'key'        => 'test_total',
        ];

        $orderStatus = new OrderTotal($data);
        $errors = [];
        try {
            $orderStatus->validate($data);
            $orderStatus->save();

        } catch (ValidationException $e) {
            $errors = $orderStatus->errors()['validation'];
        }
        $this->assertCount(0, $errors);

        $orderStatus->forceDelete();
    }
}