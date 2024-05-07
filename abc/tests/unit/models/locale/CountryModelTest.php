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

use abc\models\locale\Country;
use Illuminate\Validation\ValidationException;
use Tests\unit\ATestCase;

/**
 * Class CountryModelTest
 */
class CountryModelTest extends ATestCase
{

    public function testValidator()
    {

        $country = new Country(
            [
                'country_id' => 0,
                'iso_code_2' => 121111111,
                'iso_code_3' => 11111111,
                'address_format' => 111111111,
                'status' => 'fgfgf',
                'sort_order' => 'fgnbfgnfgn',
            ]
        );
        $errors = [];
        try {
            $country->validate();
        } catch (ValidationException $e) {
            $errors = $country->errors()['validation'];
        }

        $this->assertCount(6, $errors);

        $country = new Country(
            [
                'iso_code_2' => 'fd',
                'iso_code_3' => 'fdd',
                'address_format' => 'somestring',
                'status' => 1,
                'sort_order' => 2,
            ]
        );
        $errors = [];
        try {
            $country->validate();
        } catch (ValidationException $e) {
            $errors = $country->errors()['validation'];
        }

        $this->assertCount(0, $errors);

    }
}