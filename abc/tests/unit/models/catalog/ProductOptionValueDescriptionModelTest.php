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

use abc\models\catalog\ProductOptionValueDescription;
use Illuminate\Validation\ValidationException;
use Tests\unit\ATestCase;

/**
 * Class ProductOptionValueDescriptionModelTest
 */
class ProductOptionValueDescriptionModelTest extends ATestCase
{

    public function testValidator()
    {
        $valueDescription = new ProductOptionValueDescription();
        $errors = [];
        try {
            $data = [
                'language_id'             => false,
                'product_id'              => false,
                'product_option_value_id' => false,
                'name'                    => false,
                'grouped_attribute_names' => false,
            ];
            $valueDescription->validate($data);
        } catch (ValidationException $e) {
            $errors = $valueDescription->errors()['validation'];
        }

        $this->assertCount(4, $errors);

        $errors = [];
        try {
            $data = [
                'language_id'             => 1,
                'product_id'              => 50,
                'product_option_value_id' => 612,
                'name'                    => 'unit test option',
                'grouped_attribute_names' => ['test' => 'testttt'],
            ];
            $valueDescription->validate($data);
        } catch (ValidationException $e) {
            $errors = $valueDescription->errors()['validation'];
        }
        $this->assertCount(0, $errors);

    }
}