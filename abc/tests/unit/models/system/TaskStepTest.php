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

use abc\models\system\TaskStep;
use Illuminate\Validation\ValidationException;
use Tests\unit\ATestCase;

class TaskStepTest extends ATestCase
{
    public function testTaskStepValidation()
    {
        $task = new TaskStep();
        $errors = [];
        try {
            $data = [
                'task_id' => false,
                'sort_order' => false,
                'status' => false,
                'last_result' => false,
                'max_execution_time' => false,
            ];
            $task->validate($data);
        } catch (ValidationException $e) {
            $errors = $task->errors()['validation'];
        }
        $this->assertCount(5, $errors);

        $errors = [];
        try {
            $data = [
                'task_id' => 1,
                'sort_order' => 1,
                'status' => 1,
                'last_result' => 1,
                'max_execution_time' => 1,
            ];
            $task->validate($data);
        } catch (ValidationException $e) {
            $errors = $task->errors()['validation'];
        }
        $this->assertCount(0, $errors);
    }
}