<?php

namespace abantecart\tests;

use abc\modules\events\ABaseEvent;
use H;

/**
 * Class testOrderFulfilment
 *
 * @package abantecart\tests
 */
class TestProductExportComplete extends AbanteCartTest
{

    protected function tearDown()
    {
        //init
    }

    public function test()
    {

        $task_id = 29;

        H::event('abc\core\lib\ATaskManager@deleteTask', [new ABaseEvent($task_id)]);
    }

}