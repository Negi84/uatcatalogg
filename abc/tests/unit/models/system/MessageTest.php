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

use abc\models\system\Message;
use Illuminate\Validation\ValidationException;
use Tests\unit\ATestCase;

class MessageTest extends ATestCase
{
    public function testMessageValidation()
    {
        $massage = new Message();
        $errors = [];
        try {
            $data = [
                'viewed' => false,
                'repeated' => false,
            ];
            $massage->validate($data);
        } catch (ValidationException $e) {
            $errors = $massage->errors()['validation'];
        }
        $this->assertCount(2, $errors);

        $errors = [];
        try {
            $data = [
                'viewed' => 1,
                'repeated' => 1,
            ];
            $massage->validate($data);
        } catch (ValidationException $e) {
            $errors = $massage->errors()['validation'];
        }
        $this->assertCount(0, $errors);
    }
}