<?php

namespace abc\extensions\tims_catalog\modules\workers;

use abc\core\ABC;
use abc\core\engine\Registry;
use abc\core\lib\ADB;
use abc\core\lib\AException;
use abc\extensions\tims_catalog\modules\traits\ProductExportTrait;
use abc\models\catalog\Product;
use abc\modules\workers\ABaseWorker;
use H;
use Illuminate\Database\Connection;




/**
 * Class TimsImport
 *
 * @package abc\extensions\tims_catalog\modules
 */
class ProductExport extends ABaseWorker
{
    use ProductExportTrait;
    /**
     * @var ADB | Connection
     */
    private $db;
    private $batchID = 0;
    private $lockFile;
    private $logFile;
    private $workerDir = '';
    private $workingFile = '';
    const DATE_FIELDS = ['date_available', 'date_deleted'];


    public function __construct()
    {
        parent::__construct();
        $this->reRunIfFailed = true;
        $this->registry = Registry::getInstance();
        $this->db = $this->registry->get('db');
    }

    private function init($batchID)
    {
        $this->batchID = $batchID;
        if ($batchID) {
            $this->workingFile = ABC::env('DIR_SYSTEM') . 'export' . DS . 'auto_export_products_' . $batchID;
            if (!is_file($this->workingFile)) {
                throw new AException("File " . $this->workingFile . " with product IDs not found. Nothing to process");
            }
        } else {
            $this->batchID = 'export-all-products';
        }

        $this->workerDir = ABC::env('DIR_SYSTEM') . 'export' . DS . 'export_product_' . date('YmdHi') . DS;
        if (!H::mkDir($this->workerDir)) {
            throw new AException(
                "Directory " . ABC::env('DIR_SYSTEM') . 'export' . " is not writable! Change permissions and try again."
            );
        }

        //check concurrent process
        $this->lockFile = ABC::env('DIR_SYSTEM') . 'export' . DS . 'ProductExportWorker' . $this->batchID . '.lock';
        if (is_file($this->lockFile)) {
            throw new AException('Another worker of batch ID ' . $this->batchID . ' is running. Skipped.');
        }
        //if ok - run
        $lock = @fopen($this->lockFile, 'a+');
        @fclose($lock);

        $this->logFile = @fopen($this->workerDir . 'ProductExport.log', 'a+');

        // if all fine - run export of all categories first
        if ($this->workingFile) {
            $file = fopen($this->workingFile, 'r');
            $product_ids = $this->ejectProducts($file)['products'];
            fclose($file);
        } else {
            $product_ids = $this->ejectAllDatabaseProducts()['products'];
        }

        $tmp = Product::with('categories')->whereIn('product_id', $product_ids)->get()?->toArray();
        $categoryIds = [];
        foreach ($tmp as $item) {
            foreach ($item['categories'] as $category) {
                $categoryIds[] = $category['category_id'];
            }
        }
        $categoryIds = array_unique($categoryIds);

        $worker = new CategoryExport();
        $result = $worker->exportAll($categoryIds);
        if (!$result['result']) {
            throw new AException('Export of All Categories before product export failed. Stopped.');
        }
    }

    protected function ejectProducts($file)
    {
        $product_ids = $sites = [];
        while (($items = fgetcsv($file, 0, ';')) !== false) {
            $id = (int)$items[0];
            $product_ids[$id] = $id;
            if (isset($items[1])) {
                $sites[$id][$items[1]]['product_type'] = $items[2];
                $sites[$id][$items[1]]['uplift_id'] = $items[3];
            }
        }
        return ['products' => $product_ids, 'sites' => $sites];
    }

    protected function ejectAllDatabaseProducts()
    {
        $product_ids = $sites = [];
        $products = Product::select(['product_id', 'sites', 'product_type', 'uplift_id'])->get();
        foreach ($products as $row) {
            $id = $row->product_id;
            $product_ids[$id] = $id;
            foreach ($row->sites as $site) {
                $sites[$id][$site]['product_type'] = $row->product_type[$site];
                $sites[$id][$site]['uplift_id'] = $row->uplift_id[$site];
            }
        }
        return ['products' => $product_ids, 'sites' => $sites];
    }

    // php abcexec job:run --worker=productExport --method=export --batch-id=20180506204646
    public function export()
    {
        $this->batchID = func_get_arg(0)['batch-id'];
        $this->init($this->batchID);

        $file = fopen($this->workingFile, 'r');
        $result = $this->ejectProducts($file);
        fclose($file);
        $product_ids = $result['products'];
        $sites = $result['sites'];


        if (!$product_ids) {
            $this->toLog("Export list from file " . $this->workingFile . " is empty. Nothing to process");
            $this->cleanup();
            return false;
        }
        $inputDir = $this->workerDir . 'input' . DS;
        H::mkDir($inputDir);
        @copy($this->workingFile, $inputDir . basename($this->workingFile));

        $this->toLog("Start products processing with batch {$this->batchID}");

        $result = $this->processBatch($product_ids, $sites);
        $this->cleanup();
        return $result;
    }

    // php abcexec job:run --worker=productExport --method=exportAll
    public function exportAll()
    {
        $this->init('');

        $result = $this->ejectAllDatabaseProducts();
        $product_ids = $result['products'];
        $sites = $result['sites'];


        if (!$product_ids) {
            $this->toLog("Export list from file " . $this->workingFile . " is empty. Nothing to process");
            $this->cleanup();
            return false;
        }

        $this->toLog("Start products processing with batch {$this->batchID}");

        $result = $this->processBatch($product_ids, $sites);
        $this->cleanup();
        return $result;
    }

    protected function toLog($text)
    {
        fputs($this->logFile, $text . "\n");
        $this->echoCli($text);
    }

    /**
     * @param string
     * @void
     */
    public function error($errorText)
    {
        @file_put_contents($this->workingFile.DS.$this->logFile, $errorText."\n", FILE_APPEND);
        parent::error($errorText);
    }

    /**
     * @void
     */
    public function cleanup()
    {
        @unlink($this->lockFile);
        @fclose($this->logFile);
    }

    /**
     * @void
     */
    public function postProcessing()
    {
       /* @unlink($this->workingDir.DS.$this->lockFile);
        if (\H::dirIsEmpty($this->workingDir)) {
            @unlink($this->workingDir);
        }*/
    }

    /**
     * @return array
     */
    public function getModuleMethods()
    {
        return ['export', 'exportAll'];
    }
}