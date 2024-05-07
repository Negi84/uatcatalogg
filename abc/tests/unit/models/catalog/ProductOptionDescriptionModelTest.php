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

use abc\models\catalog\ProductOptionDescription;
use Illuminate\Validation\ValidationException;
use Tests\unit\ATestCase;

/**
 * Class ProductOptionDescriptionModelTest
 */
class ProductOptionDescriptionModelTest extends ATestCase
{

    public function testValidator()
    {
        $productOptionDescription = new ProductOptionDescription();
        $errors = [];
        try {
            $data = [
                'language_id'        => false,
                'product_id'         => false,
                'product_option_id'  => false,
                'name'               => false,
                'option_placeholder' => false,
                'error_text'         => false,
            ];
            $productOptionDescription->validate($data);
        } catch (ValidationException $e) {
            $errors = $productOptionDescription->errors()['validation'];
        }

        $this->assertCount(6, $errors);

        $errors = [];
        try {
            $data = [
                'language_id'        => 1,
                'product_id'         => 50,
                'product_option_id'  => 307,
                'name'               => 'unit test option',
                'option_placeholder' => 'some placeholder text',
                'error_text'         => 'Oops..you did something wrong.',
            ];
            $productOptionDescription->validate($data);
        } catch (ValidationException $e) {
            $errors = $productOptionDescription->errors()['validation'];
        }
        $this->assertCount(0, $errors);

        $errors = [];
        try {
            $data = [
                'language_id'        => 555599999,
                'product_id'         => 1555888,
                'product_option_id'  => 0,
                'name'               => 'unit test option',
                'option_placeholder' => 'some placeholder text',
                'error_text'         => 'Oops..you did something wrong.',
            ];
            $productOptionDescription->validate($data);
        } catch (ValidationException $e) {
            $errors = $productOptionDescription->errors()['validation'];
        }
        $this->assertCount(3, $errors);

    }
}