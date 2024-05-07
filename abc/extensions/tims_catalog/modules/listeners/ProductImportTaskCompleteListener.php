<?php

namespace abc\extensions\tims_catalog\modules\listeners;

use abc\core\ABC;
use abc\core\engine\Registry;
use abc\core\lib\AException;
use abc\core\lib\JobManager;
use abc\extensions\tims_catalog\modules\workers\ProductExport;
use abc\modules\events\ABaseEvent;
use H;

class ProductImportTaskCompleteListener
{

    protected $registry;

    public function __construct()
    {
        $this->registry = Registry::getInstance();
    }

    /**
     * @param ABaseEvent $event
     *
     * @return array
     * @throws \Exception
     */
    public function handle(ABaseEvent $event)
    {
        $task_id = $event->args[0];
        $exportDir = ABC::env('DIR_SYSTEM').'export'.DS;
        $fileName = 'auto_export_products_'.$task_id;
        $filePath = $exportDir.$fileName;

        if ($task_id && is_file($filePath)) {

            if (!filesize($filePath)) {
                @unlink($filePath);
                return [
                    'result'  => false,
                    'message' => "File ".$filePath." is empty",
                ];
            }
            //if all fine - create new job
            $user = $this->registry->get('user');
            /**
             * @var JobManager $jm
             */
            try {
                $jm = ABC::getObjectByAlias('JobManager', [$this->registry]);
                $job_id = $jm->addJob(
                    [
                        'name'          => 'AutoExportAfterImportProducts initiated by task ID '
                            .$task_id.'. Batch ID: '.$task_id,
                        'actor_type'    => 1, //admin
                        'actor_id'      => $user ? $user->getId() : 0,
                        'actor_name'    => $user ? $user->getUserFirstName().' '.$user->getUserLastName() : 'n/a',
                        'status'        => $jm::STATUS_READY,
                        'configuration' => [
                            'worker'           => [
                                'file'       =>
                                    ABC::env('DIR_APP_EXTENSIONS')
                                    .'tims_catalog'.DS
                                    .'modules'.DS
                                    .'workers'.DS
                                    .'ProductExport.php',
                                'class'      => ProductExport::class,
                                'method'     => 'export',
                                'parameters' => ['batch-id' => $task_id],
                            ],
                            'export_list_file' => $fileName,
                        ],
                    ]
                );
            } catch (\Exception $e) {
                $messages = $this->registry->get('messages');
                if ($messages) {
                    $messages->saveWarning(__CLASS__, 'Job After Products Import not created! Task ID '.$task_id);
                }
                $this->registry->get('log')->write(__CLASS__.': '.$e->getTraceAsString());
            }
        }
        return ['result' => true];
    }
}