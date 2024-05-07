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

use abc\models\system\Job;
use Illuminate\Validation\ValidationException;
use Tests\unit\ATestCase;

class JobTest extends ATestCase
{
    public function testJobValidation()
    {
        $job = new Job();
        $errors = [];
        try {
            $data = [
                'status' => false,
                'last_result' => false,
                'actor_type' => false,
                'actor_id' => false,
            ];
            $job->validate($data);
        } catch (ValidationException $e) {
            $errors = $job->errors()['validation'];
        }
        $this->assertCount(4, $errors);

        $errors = [];
        try {
            $data = [
                'category_id' => 1,
                'uuid' => 'uuiidddd',
                'parent_id' => 36,
                'path' => '36_22',
                'total_products_count' => 1,
                'active_products_count' => 1,
                'children_count' => 0,
                'sort_order' => 1,
                'status' => true,
            ];
            $job->validate($data);
        } catch (ValidationException $e) {
            $errors = $job->errors()['validation'];
        }
        $this->assertCount(0, $errors);
    }
}