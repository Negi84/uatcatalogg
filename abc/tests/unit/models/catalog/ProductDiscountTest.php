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

namespace Tests\unit\models\catalog;

use abc\models\catalog\Product;
use abc\models\catalog\ProductDiscount;
use abc\models\customer\CustomerGroup;
use Illuminate\Validation\ValidationException;
use Tests\unit\ATestCase;

class ProductDiscountTest extends ATestCase
{
    public function testValidator()
    {
        $product = new ProductDiscount(
            [
                'product_id'        => false,
                'customer_group_id' => false,
                'quantity'          => false,
                'priority'          => false,

            ]
        );
        $errors = [];
        try {
            $product->validate();
        } catch (ValidationException $e) {
            $errors =  $product->errors()['validation'];
        }

        $this->assertCount(4, $errors);

        $product = Product::first();
        $cGroup = CustomerGroup::first();
        $product = new ProductDiscount(
            [
                'product_id'        => $product->product_id,
                'customer_group_id' => $cGroup->customer_group_id,
                'quantity'          => 1,
                'priority'          => 1,

            ]
        );
        $errors = [];
        try {
            $product->validate();
        } catch (ValidationException $e) {
            $errors =  $product->errors()['validation'];
        }
        $this->assertCount(0, $errors);

    }
}
