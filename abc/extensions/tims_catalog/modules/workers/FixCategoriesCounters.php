<?php

namespace abc\extensions\tims_catalog\modules\workers;

use abc\core\ABC;
use abc\core\engine\Registry;
use abc\core\lib\ADB;
use abc\extensions\tims_catalog\modules\traits\ProductExportTrait;
use abc\models\catalog\Category;
use abc\modules\workers\ABaseWorker;
use Illuminate\Database\Connection;

class FixCategoriesCounters extends ABaseWorker
{
    use ProductExportTrait;

    protected $registry;
    /**
     * @var ADB | Connection
     */
    private $db;
    private $batchID = 0;
    private $logFile;
    private $workerDir = '';
    private $workingFile = '';
    const DATE_FIELDS = ['date_available', 'date_deleted'];

    private $lockFile = 'FixCategoriesCountersWorker.lock';

    public function __construct()
    {
        parent::__construct();
        $this->registry = Registry::getInstance();
        $this->db = $this->registry->get('db');
    }

    public function getModuleMethods()
    {
        return ['main'];
    }

    public function postProcessing()
    {
        @unlink($this->lockFile);
    }

    // php abcexec job:run --worker=FixCategoriesCounters [ optional] --touch-products
    public function main($params = [])
    {
        $this->init();
        $categories = Category::all();
        foreach ($categories as $category) {
            $this->echoCli('Process category: ' . $category->category_id);
            if ($params['touch-products']) {

                $ids = $category->products->pluck('product_id')->toArray();
                $this->echoCli('Process product batch.(' . count($ids) . ')');
                $this->processBatch($ids);
            } else {
                $this->echoCli('Process "touch"');
                $category->touch();
            }
        }

    }

    private function init(): bool
    {
        $this->lockFile = ABC::env('DIR_SYSTEM') . $this->lockFile;
        if (is_file($this->lockFile)) {
            $pid = file_get_contents($this->lockFile);
            exit ('Another worker with process ID ' . $pid . ' is running. Skipped.');
        }

        return true;
    }

    /**
     * @param string $text
     *
     * @void
     */
    public function echoCli($text)
    {
        if ($this->outputType == 'cli') {
            echo $text . $this->EOF;
        } else {
            $this->output[] = $text;
        }
    }

    protected function toLog($text)
    {
        $this->echoCli($text);
    }
}