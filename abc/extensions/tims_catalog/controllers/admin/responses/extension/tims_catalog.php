<?php
/*------------------------------------------------------------------------------
  $Id$

  AbanteCart, Ideal OpenSource Ecommerce Solution
  http://www.AbanteCart.com

  Copyright © 2011-2018 Belavier Commerce LLC

  This source file is subject to Open Software License (OSL 3.0)
  License details is bundled with this package in the file LICENSE.txt.
  It is also available at this URL:
  <http://www.opensource.org/licenses/OSL-3.0>

 UPGRADE NOTE:
   Do not edit or add to this file if you wish to upgrade AbanteCart to newer
   versions in the future. If you wish to customize AbanteCart for your
   needs please refer to http://www.AbanteCart.com for more information.
------------------------------------------------------------------------------*/

namespace abc\controllers\admin;

use abc\core\ABC;
use abc\core\engine\AController;
use abc\core\engine\AResource;
use abc\core\lib\AError;
use abc\core\lib\AFilter;
use abc\core\lib\AJson;
use abc\core\lib\ATaskManager;
use abc\extensions\tims_catalog\models\admin\extension\ModelExtensionTimsCatalog;
use H;
use stdClass;

/**
 * Class ControllerResponsesExtensionTimsCatalog
 *
 * @package abc\controllers\admin
 * @property ModelExtensionTimsCatalog $model_extension_tims_catalog
 */
class ControllerResponsesExtensionTimsCatalog extends AController
{
    public $errors = [];
    CONST  TASK_NAME = 'tims_catalog_syncing';

    public function buildTask()
    {
        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);
        $this->data['output'] = [];
        $this->loadLanguage('tims_catalog/tims_catalog');

        if ($this->validate()) {
            $this->loadModel('extension/tims_catalog');

            $task_details = $this->model_extension_tims_catalog->createTask(
                self::TASK_NAME,
                ['products' => $this->request->get['products']]
            );
            $task_api_key = $this->config->get('task_api_key');

            if (!$task_details) {
                $this->errors = array_merge($this->errors, $this->model_extension_tims_catalog->errors);
                $error = new AError('tims syncing task error');
                return $error->toJSONResponse(
                    'APP_ERROR_402',
                    [
                        'error_text'  => implode(' ', $this->errors),
                        'reset_value' => true,
                    ]
                );
            } elseif (!$task_api_key) {
                $error = new AError('tims syncing task error');
                return $error->toJSONResponse(
                    'APP_ERROR_402',
                    [
                        'error_text'  => 'Please set up Task API Key in the settings!',
                        'reset_value' => true,
                    ]
                );
            } else {
                $task_details['task_api_key'] = $task_api_key;
                $task_details['url'] = ABC::env('HTTPS_SERVER').'task.php';
                $this->data['output']['task_details'] = $task_details;
            }

        } else {
            $error = new AError('tims syncing task error');
            return $error->toJSONResponse(
                'APP_ERROR_402',
                [
                    'error_text'  => implode(' ', $this->errors),
                    'reset_value' => true,
                ]
            );
        }

        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);

        $this->load->library('json');
        $this->response->addJSONHeader();
        $this->response->setOutput(AJson::encode($this->data['output']));
    }

    private function validate()
    {
        if (!$this->user->canModify('extension/tims_catalog')) {
            $this->errors['warning'] = $this->language->get('error_permission');
        }

        $this->extensions->hk_ValidateData($this);
        return (!$this->errors);
    }

    /**
     * post-trigger of task
     */
    public function completeTask()
    {
        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $task_id = (int)$this->request->post['task_id'];
        if ($task_id) {
            $tm = new ATaskManager();
            $tm->deleteTask($task_id);
        }
        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);

        $this->cache->flush();

        $this->load->library('json');
        $this->response->addJSONHeader();
        $this->response->setOutput(
            AJson::encode(
                [
                    'result'      => true,
                    'result_text' => 'Product is synced!',
                ]
            )
        );
    }

    public function abort()
    {
        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $task_id = (int)$this->request->post['task_id'];
        if (!$task_id) {
            return null;
        }

        //check task result
        $tm = new ATaskManager();
        $task_info = $tm->getTaskById($task_id);

        if ($task_info) {
            $tm->deleteTask($task_id);
            $result_text = $this->language->get('text_success_abort');
        } else {
            $error_text = 'Task #'.$task_id.' not found!';
            $error = new AError($error_text);
            return $error->toJSONResponse(
                'APP_ERROR_402',
                [
                    'error_text'  => $error_text,
                    'reset_value' => true,
                ]
            );
        }

        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);

        $this->load->library('json');
        $this->response->addJSONHeader();
        $this->response->setOutput(
            AJson::encode(
                [
                    'result'      => true,
                    'result_text' => $result_text,
                ]
            )
        );
    }

    public function restartTask()
    {
        $this->data['output'] = [];
        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $task_id = (int)$this->request->get_or_post('task_id');
        $task_api_key = $this->config->get('task_api_key');
        $etas = [];
        if ($task_id) {
            $tm = new ATaskManager();

            $steps = $tm->getTaskSteps($task_id);
            foreach ($steps as $step) {
                $tm->updateStep($step['step_id'], ['status' => 1]);
                $etas[$step['step_id']] = $step['max_execution_time'];
            }

            $task_details = $tm->getTaskById($task_id);
            if (!$task_details || !$task_details['steps']) {
                //remove task when it does not contain steps
                if (!$task_details['steps']) {
                    $tm->deleteTask($task_id);
                }
                $error_text = "Error: Cannot to restart task #".$task_id.'. Task removed.';
                $error = new AError($error_text);
                return $error->toJSONResponse(
                    'APP_ERROR_402',
                    [
                        'error_text'  => $error_text,
                        'reset_value' => true,
                    ]
                );
            } elseif (!$task_api_key) {
                $error = new AError('Tims Product sync error');
                return $error->toJSONResponse(
                    'APP_ERROR_402',
                    [
                        'error_text'  => 'Please set up Task API Key in the settings!',
                        'reset_value' => true,
                    ]
                );
            } else {
                $task_details['task_api_key'] = $task_api_key;
                $task_details['url'] = ABC::env('HTTPS_SERVER').'task.php';
                //change task status
                $task_details['status'] = $tm::STATUS_READY;
                $tm->updateTask($task_id, ['status' => $tm::STATUS_READY]);
            }

            foreach ($etas as $step_id => $eta) {
                $task_details['steps'][$step_id]['eta'] = $eta;
            }

            $this->data['output']['task_details'] = $task_details;

        } else {
            $error = new AError(implode('<br>', $this->errors));
            return $error->toJSONResponse(
                'VALIDATION_ERROR_406',
                [
                    'error_text'  => 'Unknown task ID.',
                    'reset_value' => true,
                ]
            );
        }

        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);

        $this->load->library('json');
        $this->response->addJSONHeader();
        $this->response->setOutput(AJson::encode($this->data['output']));

    }

}