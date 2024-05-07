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

use abc\models\locale\CountryDescription;
use Illuminate\Validation\ValidationException;
use Tests\unit\ATestCase;

/**
 * Class CountryDescriptionModelTest
 */
class CountryDescriptionModelTest extends ATestCase
{


    public function testValidator()
    {

        $country = new CountryDescription(
            [
                'id' => -1,
                'name' => '',
                'language_id' => 'fvf',
            ]
        );
        $errors = [];
        try {
            $country->validate();
        } catch (ValidationException $e) {
            $errors = $country->errors()['validation'];
        }

        $this->assertCount(3, $errors);


        $country = new CountryDescription(
            [
                'id' => 2,
                'name' => 'somestring',
                'language_id' => 1,
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