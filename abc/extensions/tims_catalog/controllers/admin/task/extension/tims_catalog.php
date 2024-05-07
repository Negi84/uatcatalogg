<?php

namespace abc\controllers\admin;

use abc\core\ABC;
use abc\core\engine\AController;
use abc\core\engine\Registry;
use abc\core\lib\AJson;
use abc\extensions\tims_catalog\modules\traits\ProductExportTrait;


/**
 * Class ControllerTaskExtensionTimsCatalog
 *
 * @package abc\controllers\admin
 *
 */

class ControllerTaskExtensionTimsCatalog extends AController
{
    use ProductExportTrait;
    protected $registry;
    const DATE_FIELDS = ['date_available'];
    public function __construct(Registry $registry, $instance_id, $controller, $parent_controller = '')
    {
        $this->registry = $registry;
        parent::__construct($registry, $instance_id, $controller, $parent_controller);

    }

    public function export($task_id, $step_id, $settings = []){

        $product_ids = (array)$settings['products'];

        $excludeFields = (array)$settings['excludeFields'];
        $output = $this->processBatch($product_ids, [], $excludeFields);

        $this->load->library('json');
        $this->response->addJSONHeader();
        $this->response->setOutput(AJson::encode($output));
    }

    public function delete($task_id, $step_id, $settings){

        $products = (array)$settings['products'];
        $output = $this->deleteBatch($products);

        $this->load->library('json');
        $this->response->addJSONHeader();
        $this->response->setOutput(AJson::encode($output));
    }

    protected function toLog($text){
        $log = Registry::getInstance()->get('log');
        if(!$log){
            $log = ABC::getObjectByAlias('ALog');
        }
        $log?->write($text);
    }
}
