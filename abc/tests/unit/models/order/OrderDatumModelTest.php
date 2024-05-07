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

use abc\models\order\OrderDatum;
use Illuminate\Validation\ValidationException;
use Tests\unit\ATestCase;

/**
 * Class OrderDatumModelTest
 */
class OrderDatumModelTest extends ATestCase
{

    protected function setUp():void
    {
        //init
    }

    public function testValidator()
    {
        //validate
        $data = [
            'type_id'  => 'fail',
            'order_id' => -0.000000000123232,
            'data'     => 'fail',
        ];
        $orderDatum = new OrderDatum($data);
        $errors = [];
        try {
            $orderDatum->validate();
        } catch (ValidationException $e) {
            $errors = $orderDatum->errors()['validation'];
        }

        $this->assertCount(2, $errors);

        //check validation of presence in database
        $data = [
            'type_id'     => 122,
            'language_id' => 1500,
        ];
        $orderDatum = new OrderDatum($data);
        $errors = [];
        try {
            $orderDatum->validate();
        } catch (ValidationException $e) {
            $errors = $orderDatum->errors()['validation'];
        }

        $this->assertCount(2, $errors);

        $data = [
            'type_id'  => 2,
            'order_id' => 2,
            'data'     => ['someData' => 'someValue'],
        ];

        $orderDatum = new OrderDatum($data);
        $errors = [];
        try {
            $orderDatum->validate();
            $orderDatum->save();
        } catch (ValidationException $e) {
            $errors = $orderDatum->errors()['validation'];
        }

        $this->assertCount(0, $errors);
        $orderDatum->forceDelete();

    }

}