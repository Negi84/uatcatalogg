<?php

namespace abc\extensions\tims_catalog\modules\listeners;

use abc\core\ABC;
use abc\core\engine\Registry;
use abc\core\lib\JobManager;
use abc\extensions\tims_catalog\modules\workers\CategoryExport;
use abc\models\catalog\Category;
use abc\modules\events\ABaseEvent;
use H;

/**
 * Class CategoryChangeListener
 *
 * @package abc\extensions\tims_catalog\modules\listeners
 */
class CategoryChangeListener
{

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * CategoryChangeListener constructor.
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
        $categoryId = $category->category_id;

        if ($categoryId) {
            $user = $this->registry->get('user');
            /**
             * @var JobManager $jm
             */
            try {
                $jm = ABC::getObjectByAlias('JobManager', [$this->registry]);
                $jm->addJob(
                    [
                        'name'          => 'CategoryChangeListener auto create job '.$categoryId,
                        'actor_type'    => 1, //admin
                        'actor_id'      => $user ? $user->getId() : 0,
                        'actor_name'    => $user ? $user->getUserFirstName().' '.$user->getUserLastName() : 'n/a',
                        'status'        => $jm::STATUS_READY,
                        'configuration' => [
                            'worker'           => [
                                'file'       =>
                                    ABC::env('DIR_APP_EXTENSIONS')
                                    .'tims_catalog'.DS
                                    . 'modules' . DS
                                    . 'workers' . DS
                                    . 'CategoryExport.php',
                                'class'      => CategoryExport::class,
                                'method'     => 'export',
                                'parameters' => ['category_id' => $categoryId],
                            ],
                        ],
                    ]
                );
            } catch (\Exception|\Error $e) {
                $messages = $this->registry->get('messages');
                if ($messages) {
                    $messages->saveWarning(__CLASS__, 'Job After Category saved not created! ' . $e->getMessage());
                }
                $this->registry->get('log')->write(__CLASS__ . ': ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            }
        }
        return ['result' => true];
    }
}

