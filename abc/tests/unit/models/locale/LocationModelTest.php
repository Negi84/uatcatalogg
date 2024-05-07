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

use Illuminate\Validation\ValidationException;
use abc\models\locale\Location;
use Tests\unit\ATestCase;

/**
 * Class LocationModelTest
 */
class LocationModelTest extends ATestCase
{
    public function testValidator()
    {
        $location = new Location(
            [
                'location_id' => 214748364711111111,
                'name' => 'somestringsomestringsomestringsomestringsomestringsomestringsomestringsomestringsomestring',
                'description' => ''
            ]
        );
        $errors = [];
        try {
            $location->validate();
        } catch (ValidationException $e) {
            $errors = $location->errors()['validation'];
        }
        $this->assertCount(3, $errors);

        $location = new Location(
            [
                'location_id' => 2,
                'name' => 'USA',
                'description' => 'All States'
            ]
        );
        $errors = [];
        try {
            $location->validate();
        } catch (ValidationException $e) {
            $errors = $location->errors()['validation'];
        }
        $this->assertCount(0, $errors);
    }
}