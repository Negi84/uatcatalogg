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

use abc\models\catalog\ProductTag;
use Illuminate\Validation\ValidationException;
use Tests\unit\ATestCase;

/**
 * Class ProductTagModelTest
 */
class ProductTagModelTest extends ATestCase
{

    public function testValidator()
    {
        $productsRelated = new ProductTag();
        $errors = [];
        try {
            $data = [
                'product_id'  => false,
                'language_id' => false,
                'tag'         => null,
            ];
            $productsRelated->validate($data);
        } catch (ValidationException $e) {
            $errors = $productsRelated->errors()['validation'];
        }

        $this->assertCount(3, $errors);

        $errors = [];
        try {
            $data = [
                'product_id'  => 50,
                'language_id' => 1,
                'tag'         => 'sometesttag',
            ];
            $productsRelated->validate($data);
        } catch (ValidationException $e) {
            $errors = $productsRelated->errors()['validation'];
        }

        $this->assertCount(0, $errors);

        $errors = [];
        try {
            $data = [
                'product_id'  => 5000000,
                'language_id' => 1,
                'tag'         => 'sometesttag',
            ];
            $productsRelated->validate($data);
        } catch (ValidationException $e) {
            $errors = $productsRelated->errors()['validation'];
        }

        $this->assertCount(1, $errors);

        $errors = [];
        try {
            $data = [
                'product_id'  => 50,
                'language_id' => 522222,
                'tag'         => 'sometesttag',
            ];
            $productsRelated->validate($data);
        } catch (ValidationException $e) {
            $errors = $productsRelated->errors()['validation'];
        }

        $this->assertCount(1, $errors);

        $errors = [];
        try {
            $data = [
                'product_id'  => 50,
                'language_id' => 1,
                'tag'         => '',
            ];
            $productsRelated->validate($data);
        } catch (ValidationException $e) {
            $errors = $productsRelated->errors()['validation'];
        }

        $this->assertCount(1, $errors);

    }
}