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

use abc\models\catalog\GlobalAttributesValueDescription;
use Illuminate\Validation\ValidationException;
use Tests\unit\ATestCase;

class GlobalAttributesValueDescriptionTest extends ATestCase
{
    public function testValidator()
    {
        $attr = new GlobalAttributesValueDescription();
        $errors = [];
        try {
            $data = [
                'attribute_value_id' => false,
                'attribute_id' => false,
                'language_id' => false,
            ];
            $attr->validate($data);
        } catch (ValidationException $e) {
            $errors = $attr->errors()['validation'];
        }
        $this->assertCount(3, $errors);

        $errors = [];
        try {
            $data = [
                'attribute_value_id' => 1,
                'attribute_id' => 36,
                'language_id' => 1,
            ];
            $attr->validate($data);
        } catch (ValidationException $e) {
            $errors = $attr->errors()['validation'];
        }
        $this->assertCount(0, $errors);
    }
}
