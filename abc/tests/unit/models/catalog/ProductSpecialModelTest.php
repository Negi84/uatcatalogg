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

use abc\models\catalog\ProductSpecial;
use Illuminate\Validation\ValidationException;
use Tests\unit\ATestCase;

/**
 * Class ProductSpecialModelTest
 */
class ProductSpecialModelTest extends ATestCase
{

    public function testValidator()
    {
        $productSpecial = new ProductSpecial();
        $errors = [];
        try {
            $data = [
                'product_id'        => false,
                'customer_group_id' => false,
                'priority'          => false,
                'price'             => false,
                'date_start'        => '0000-00-00-000',
                'date_end'          => '0000-00-00-000',
            ];
            $productSpecial->validate($data);
        } catch (ValidationException $e) {
            $errors = $productSpecial->errors()['validation'];
        }

        $this->assertCount(6, $errors);

        $errors = [];
        try {
            $data = [
                'product_id'        => 50,
                'customer_group_id' => 1,
                'priority'          => 1,
                'price'             => 10.12,
                'date_start'        => date('Y-m-d H:i:s'),
                'date_end'          => date('Y-m-d H:i:s'),
            ];
            $productSpecial->validate($data);
        } catch (ValidationException $e) {
            $errors = $productSpecial->errors()['validation'];
        }
        $this->assertCount(0, $errors);

        $errors = [];
        try {
            $data = [
                'product_id'        => 50,
                'customer_group_id' => 1,
                'priority'          => 1,
                'price'             => 10.12,
                'date_start'        => null,
                'date_end'          => null,
            ];
            $productSpecial->validate($data);
        } catch (ValidationException $e) {
            $errors = $productSpecial->errors()['validation'];
        }
        $this->assertCount(0, $errors);
    }
}