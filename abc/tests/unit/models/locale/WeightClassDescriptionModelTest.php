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

namespace Tests\unit\models\locale;

use abc\models\locale\WeightClassDescription;
use Illuminate\Validation\ValidationException;
use Tests\unit\ATestCase;

/**
 * Class WeightClassDescriptionModelTest
 */
class WeightClassDescriptionModelTest extends ATestCase
{

    public function testValidator()
    {

        $weight = new WeightClassDescription(
            [
                'id' => 0,
                'title' => 4567890876543,
                'unit' => 'ffffffff',

            ]
        );
        $errors = [];
        try {
            $weight->validate();
        } catch (ValidationException $e) {
            $errors = $weight->errors()['validation'];
        }
        $this->assertCount(3, $errors);

        $weight = new WeightClassDescription(
            [

                'title' => 'somestring',
                'unit' => 'str',
            ]
        );
        $errors = [];
        try {
            $weight->validate();
        } catch (ValidationException $e) {
            $errors = $weight->errors()['validation'];
        }
        $this->assertCount(0, $errors);

    }
}