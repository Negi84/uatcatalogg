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

use abc\models\locale\Country;
use abc\models\order\OrderStatus;
use Illuminate\Validation\ValidationException;
use Tests\unit\ATestCase;

/**
 * Class OrderStatusModelTest
 */
class OrderStatusModelTest extends ATestCase
{

    public function testValidator()
    {

        //validate
        $data = [
            'status_text_id' => -0.000000000000000009,
        ];

        $orderStatus = new OrderStatus();
        $errors = [];
        try {
            $orderStatus->validate($data);
        } catch (ValidationException $e) {
            $errors = $orderStatus->errors()['validation'];
        }
        $this->assertCount(1, $errors);

        //validate
        $data = [
            'status_text_id' => 'test_status',
            'display_status' => false,
        ];

        $orderStatus = new OrderStatus($data);
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