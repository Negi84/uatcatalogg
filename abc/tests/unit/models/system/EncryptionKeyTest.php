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

use abc\models\system\EncryptionKey;
use Illuminate\Validation\ValidationException;
use Tests\unit\ATestCase;

class EncryptionKeyTest extends ATestCase
{
    public function testEncryptionKeyValidation()
    {
        $encryption = new EncryptionKey();
        $errors = [];
        try {
            $data = [
                'status' => false,
            ];
            $encryption->validate($data);
        } catch (ValidationException $e) {
            $errors = $encryption->errors()['validation'];
        }
        $this->assertCount(1, $errors);

        $errors = [];
        try {
            $data = [
                'status' => 1,
            ];
            $encryption->validate($data);
        } catch (ValidationException $e) {
            $errors = $encryption->errors()['validation'];
        }
        $this->assertCount(0, $errors);
    }
}