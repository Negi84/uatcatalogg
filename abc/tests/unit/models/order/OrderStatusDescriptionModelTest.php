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

use abc\models\order\OrderStatus;
use abc\models\order\OrderStatusDescription;
use Illuminate\Validation\ValidationException;
use Tests\unit\ATestCase;

/**
 * Class OrderStatusDescriptionModelTest
 */
class OrderStatusDescriptionModelTest extends ATestCase
{

    protected function setUp():void
    {
        //init
    }

    public function testValidator()
    {
        //validate
        $data = [
            'order_status_id' => 'fail',
            'language_id'     => 'fail',
            'name'            => -0.900000000000000000000009,
        ];

        $orderStatusDescription = new OrderStatusDescription();
        $errors = [];
        try {
            $orderStatusDescription->validate($data);
        } catch (ValidationException $e) {
            $errors = $orderStatusDescription->errors()['validation'];
        }
        $this->assertCount(3, $errors);

        //validate
        $orderStatus = new OrderStatus(['status_text_id' => 'test_status']);
        $orderStatus->save();

        $data = [
            'order_status_id' => $orderStatus->order_status_id,
            'language_id'     => 1,
            'name'            => 'Test order status description',
        ];

        $orderStatusDescription = new OrderStatusDescription($data);
        $errors = [];
        try {
            $orderStatusDescription->validate($data);
            $orderStatusDescription->save();
        } catch (ValidationException $e) {
            $errors = $orderStatusDescription->errors()['validation'];
        }
        $this->assertCount(0, $errors);

        $orderStatusDescription->forceDelete();
        $orderStatus->forceDelete();
    }
}