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

use abc\models\order\OrderHistory;
use Illuminate\Validation\ValidationException;
use Tests\unit\ATestCase;

/**
 * Class OrderHistoryModelTest
 */
class OrderHistoryModelTest extends ATestCase
{

    protected function setUp():void
    {
        //init
    }

    public function testValidator()
    {

        $errors = [];
        $order = new OrderHistory();
        try {
            //validate
            $data = [
                'order_id'        => 'fail',
                'notify'          => 'fail',
                'comment'         => [],
                'order_status_id' => 'fail',
            ];
            $order->fill($data);
            $order->validate();
        } catch (ValidationException $e) {
            $errors = $order->errors()['validation'];
        }

        $this->assertCount(4, $errors);

        //check validation of presence in database
        $data = [
            'order_id'        => 1500,
            'order_status_id' => 1500,
        ];
        $order = new OrderHistory($data);
        $errors = [];
        try {
            $order->validate();
        } catch (ValidationException $e) {
            $errors = $order->errors()['validation'];
        }

        $this->assertCount(2, $errors);

        //check validation of presence in database
        $data = [
            'order_id'        => 2,
            'order_status_id' => 1,
        ];
        $order = new OrderHistory($data);
        $errors = [];
        try {
            $order->validate();
        } catch (ValidationException $e) {
            $errors = $order->errors()['validation'];
        }

        $this->assertCount(0, $errors);

        //check correct value
        $data = [
            'order_id'        => 2,
            'order_status_id' => 1,
            'notify'          => 1,
            'comment'         => 'test order comment ',
        ];

        $order = new OrderHistory($data);
        $errors = [];
        try {
            $order->validate();
            $order->save();
        } catch (ValidationException $e) {
            $errors = $order->errors()['validation'];
        }

        $this->assertCount(0, $errors);
        $order->forceDelete();

    }
}