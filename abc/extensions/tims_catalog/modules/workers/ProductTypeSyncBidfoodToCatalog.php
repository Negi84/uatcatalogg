<?php

namespace abc\extensions\tims_catalog\modules\workers;

use abc\core\ABC;
use abc\core\engine\Registry;
use abc\modules\workers\ABaseWorker;

class ProductTypeSyncBidfoodToCatalog extends ABaseWorker
{
// Export Products from bidfood instance
// mysql -u tims -pentertims tims -e "SELECT uuid, product_type, uplift_id FROM tims.tims_products WHERE product_type<>'' INTO OUTFILE '/tmp/bidfood_products.csv' FIELDS TERMINATED BY ',' LINES TERMINATED BY '\n';"

    private $file;
    private $cfg = [
        'columns'    => [
            'uuid',
            'product_type',
            'uplift_id',
        ],
        'delimiter'  => ",",
        'start_line' => 0,
    ];
    private $db;
    private $registry;

    public function __construct()
    {
        parent::__construct();
        ini_set('auto_detect_line_endings', true);
        $this->registry = Registry::getInstance();
        $this->db = $this->registry->get('db');
    }

    public function getModuleMethods()
    {
        return ['updateProducts'];
    }

    public function postProcessing()
    {
        //unlink($this->file);
    }

    // php abcexec job:run --worker=ProductTypeSyncBidfoodToCatalog --method=updateProducts
    public function updateProducts()
    {
        $this->init();
        $this->loadFileToTable($this->file, 'products', $this->cfg);
    }

    public function init()
    {
        $this->file = ABC::env('DIR_SYSTEM').'import/bidfood_products.csv';
        if (!is_file($this->file)) {
            $this->echoCli('Not exists file path  system/import/bidfood_products.csv');
            exit(0);
        }
    }

    private function loadFileToTable($file, $table_name, $cfg)
    {
        $fh = @fopen($file, "r");
        if (!is_resource($fh)) {
            $this->error("Cannot open file ".$file);
            return false;
        }
        $count = 0;
        while (($row = fgetcsv($fh, 0, $cfg['delimiter'])) !== false) {
            $count++;
            if (count($row) === count($cfg['columns'])) {
                $row = array_map('trim', $row);

                if ($count - 1 < $cfg['start_line']) {
                    continue;
                }

                try {
                    $values = array_merge(array_combine($cfg['columns'], $row));
                    $this->db->table($table_name)->where('uuid', '=', $values['uuid'])->update([
                        'product_type' => $values['product_type'],
                        'uplift_id'    => $values['uplift_id'],
                    ]);
                } catch (\PDOException $e) {
                    $this->error($e->getMessage());
                }

            } else {
                $this->error("File {$file} Row {$count} contains incorrectly formatted data. Starts with {$row[0]}}  ");
            }
        }
        fclose($fh);
        return true;
    }
}