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

namespace Tests\unit\models\content;

use abc\models\content\Content;
use Illuminate\Validation\ValidationException;
use Tests\unit\ATestCase;

class ContentTest extends ATestCase
{
    public function testValidator()
    {
        $content = new Content();
        $errors = [];
        try {
            $data = [
                'content_id' => false,
                'parent_id' => false,
                'sort_order' => false,
            ];
            $content->validate($data);
        } catch (ValidationException $e) {
            $errors = $content->errors()['validation'];
        }
        $this->assertCount(3, $errors);

        $errors = [];
        try {
            $data = [
                'content_id' => 1,
                'parent_id'  => 2,
                'sort_order' => 1,
            ];
            $content->validate($data);
            $data = [
                'content_id' => 1,
                'parent_id'  => null,
                'sort_order' => 1,
            ];
            $content->validate($data);
        } catch (ValidationException $e) {
            $errors = $content->errors()['validation'];
        }
        $this->assertCount(0, $errors);
    }
}