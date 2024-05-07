<?php

namespace abc\extensions\tims_catalog\modules\listeners;

use abc\core\ABC;
use abc\core\engine\Registry;
use abc\core\lib\JobManager;
use abc\extensions\tims_catalog\modules\workers\CategoryExport;
use abc\models\catalog\Category;


/**
 * Class CategoryDeleteListener
 *
 * @package abc\extensions\tims_catalog\modules\listeners
 */
class CategoryDeleteListener
{

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * CategoryDeleteListener constructor.
     */
    public function __construct()
    {
        $this->registry = Registry::getInstance();
    }

    /**
     * @param Category $category
     *
     * @return array
     */
    public function handle(Category $category)
    {
        /**
         * @var Category $category
         */
        $uuid = $category->uuid;

        if ($uuid) {
            $user = $this->registry->get('user');
            /**
             * @var JobManager $jm
             */
            try {
                $jm = ABC::getObjectByAlias('JobManager', [$this->registry]);
                $jm->addJob(
                    [
                        'name'          => 'CategoryDeleteListener auto create job '.$uuid,
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
                                    .'CategoryExport.php',
                                'class'      => CategoryExport::class,
                                'method'     => 'delete',
                                'parameters' => ['uuid' => $uuid],
                            ],
                        ],
                    ]
                );
            } catch (\Exception $e) {
                $messages = $this->registry->get('messages');
                if ($messages) {
                    $messages->saveWarning(__CLASS__, 'Job After Category deleted not created! '.$e->getMessage());
                }
                $this->registry->get('log')->write(__CLASS__.': '.$e->getTraceAsString());
            }
        }
        return ['result' => true];
    }
}

