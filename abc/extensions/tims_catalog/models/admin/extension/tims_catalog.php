<?php

namespace abc\extensions\tims_catalog\models\admin\extension;

use abc\core\ABC;
use abc\core\engine\Model;
use abc\core\lib\ATaskManager;
use abc\models\catalog\Category;
use abc\models\catalog\Product;


/**
 * Class ModelExtensionTimsCatalog
 *
 * @package abc\models\admin
 *
 */
class ModelExtensionTimsCatalog extends Model
{

    public $errors = [];
    CONST STEP_MAX_TIME = 10;

    public function createTask($task_name, $data = [])
    {

        if (!$task_name) {
            $this->errors[] = 'Can not to create task. Empty task name has been given.';
        }

        $affected_products = (array)$data['products'];
        $task_controller = 'task/extension/tims_catalog/export';

        if (!$affected_products) {
            $this->errors[] = 'Can not to create task. No products found.';
            return false;
        }

        $api_destinations = ABC::env('sites');

        if (!$api_destinations) {
            $this->errors[] = 'Can not to create task. No sites found in config.';
            return false;
        }
        //check if products assigned to sites
        $products = Product::whereIn('product_id', $affected_products)->get()->toArray();
        $product_ids = array_column($products, 'product_id');
        $tm = new ATaskManager();

        //create new task
        $task_id = $tm->addTask(
            [
                'name'               => $task_name,
                'starter'            => 1, //admin-side is starter
                'created_by'         => $this->user->getId(), //get starter id
                'status'             => $tm::STATUS_READY,
                'start_time'         => date(
                    'Y-m-d H:i:s',
                    mktime(0, 0, 0, date('m'), (int)date('d') + 1, date('Y'))
                ),
                'last_time_run'      => '0000-00-00 00:00:00',
                'progress'           => '0',
                'last_result'        => '1', // think all fine until some failed step will set 0 here
                'run_interval'       => '0',
                //think that task will execute with some connection errors
                'max_execution_time' => self::STEP_MAX_TIME * sizeof($api_destinations),
            ]
        );
        if (!$task_id) {
            $this->errors = array_merge($this->errors, $tm->errors);
            return false;
        }

        //create steps
        $eta = [];

        $settings = [
            'products'          => $product_ids,
        ];
        $step_id = $tm->addStep([
            'task_id'            => $task_id,
            'sort_order'         => 1,
            'status'             => 1,
            'last_time_run'      => '0000-00-00 00:00:00',
            'last_result'        => '0',
            //think that task will execute with some connection errors
            'max_execution_time' => self::STEP_MAX_TIME,
            'controller'         => $task_controller,
            'settings'           => $settings,
        ]);
        $eta[$step_id] = self::STEP_MAX_TIME;


        $task_details = $tm->getTaskById($task_id);
        if ($task_details) {
            foreach ($eta as $step_id => $estimate) {
                $task_details['steps'][$step_id]['eta'] = $estimate;
                //remove settings from output json array. We will take it from database on execution.
                unset($task_details['steps'][$step_id]['settings']);
            }
            return $task_details;
        } else {
            $this->errors[] = 'Can not to get task details for execution';
            $this->errors = array_merge($this->errors, $tm->errors);
            return false;
        }
    }
}
