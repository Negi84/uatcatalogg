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

use abc\models\system\Task;
use Illuminate\Validation\ValidationException;
use Tests\unit\ATestCase;

class TaskTest extends ATestCase
{
    public function testTaskValidation()
    {
        $task = new Task();
        $errors = [];
        try {
            $data = [
                'starter' => false,
                'status' => false,
                'progress' => false,
                'last_result' => false,
                'run_interval' => false,
                'max_execution_time' => false,
            ];
            $task->validate($data);
        } catch (ValidationException $e) {
            $errors = $task->errors()['validation'];
        }
        $this->assertCount(6, $errors);

        $errors = [];
        try {
            $data = [
                'starter' => 1,
                'status' => 1,
                'progress' => 1,
                'last_result' => 1,
                'run_interval' => 1,
                'max_execution_time' => 1,
            ];
            $task->validate($data);
        } catch (ValidationException $e) {
            $errors = $task->errors()['validation'];
        }
        $this->assertCount(0, $errors);
    }
}