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

use abc\models\locale\ZonesToLocation;
use Illuminate\Validation\ValidationException;
use Tests\unit\ATestCase;

/**
 * Class ZoneToLocationModelTest
 */
class ZoneToLocationModelTest extends ATestCase
{
    public function testValidator()
    {

        $zone = new ZonesToLocation(
            [
                'zone_to_location_id'=> 'dsfdfdsfd',
                'country_id' => 'fgfg',
                'zone_id' => 'sdgdgsd',
                'location_id' => 'sdgsdgsd',
            ]
        );
        $errors = [];
        try {
            $zone->validate();
        } catch (ValidationException $e) {
            $errors = $zone->errors()['validation'];
        }
        $this->assertEquals(4, count($errors));

        $zone = new ZonesToLocation(
            [
                'zone_to_location_id'=>2,
                'country_id' => 3,
                'zone_id' => 1,
                'location_id' => 2,
            ]
        );
        $errors = [];
        try {
            $zone->validate();
        } catch (ValidationException $e) {
            $errors = $zone->errors()['validation'];
        }
        $this->assertEquals(0, count($errors));

    }


}