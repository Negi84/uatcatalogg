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

namespace Tests\unit\models\system;

use abc\models\system\TaxRate;
use Illuminate\Validation\ValidationException;
use Tests\unit\ATestCase;

class TaxRateTest extends ATestCase
{
    public function testTaxRateValidation()
    {
        $tax = new TaxRate();
        $errors = [];
        try {
            $data = [
                'location_id' => false,
                'zone_id' => false,
                'tax_class_id' => false,
                'priority' => false,
            ];
            $tax->validate($data);
        } catch (ValidationException $e) {
            $errors = $tax->errors()['validation'];
        }
        $this->assertCount(4, $errors);

        $errors = [];
        try {
            $data = [
                'location_id' => 1,
                'zone_id' => 1,
                'tax_class_id' => 1,
                'priority' => 1,

            ];
            $tax->validate($data);
        } catch (ValidationException $e) {
            $errors = $tax->errors()['validation'];
        }
        $this->assertCount(0, $errors);
    }
}