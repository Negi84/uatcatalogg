<?php

namespace abc\extensions\tims_catalog\modules\listeners;

use abc\core\ABC;
use abc\core\engine\Registry;
use abc\modules\events\ABaseEvent;
use abc\models\catalog\Product;
use Exception;
use H;

class ProductImportListener
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
     * @throws Exception
     */
    public function handle(ABaseEvent $event)
    {
        $task_id = $event->args[0];
        $product_id = $event->args[1];
        $record = $event->args[3];
        $sites = array_map('trim', explode(',', $record['site aliases']));
        if ($sites) {
            //prepare file for the export to sites
            $exportDir = ABC::env('DIR_SYSTEM').'export'.DS;
            H::mkDir($exportDir);
            if (!is_writable($exportDir)) {
                $error_text = "Directory " . $exportDir . " is not writable!";
                Registry::log()->error(__CLASS__ . ": " . $error_text);
                return [
                    'result'  => false,
                    'message' => $error_text,
                ];
            }
            $filename = $exportDir.'auto_export_products_'.$task_id;
            $fileHDL = fopen($filename, 'a+');

            $uplift_cats = (array)ABC::env('product')['uplift_categories'];
            $allowedUpliftIds = [];
            foreach ($uplift_cats as $key => $cat) {
                foreach ($sites as $site) {
                    $siteInfo = array_map('trim', explode('|', $site));
                    if (isset($siteInfo[0])) {
                        $site_alias = strtolower($siteInfo[0]);
                        if ($cat[$site_alias]) {
                            $allowedUpliftIds[$site_alias][] = $key;
                        }
                    }
                }
            }

            $update = [];
            $is_license = false;
            foreach ($sites as $site) {
                $siteInfo = array_map('trim', explode('|', $site));
                if (isset($siteInfo[0])) {
                    $site_alias = strtolower($siteInfo[0]);
                    $update['sites'][] = $site_alias;
                    $deliveryBand = $siteInfo[1] ?? '';
                    if ($deliveryBand) {
                        $is_license = $deliveryBand == 'E' ? true : $is_license;
                        //if at least one of type is E-Voucher - set all delivery bands as E
                        if ($is_license) {
                            $deliveryBand = 'E';
                        }
                        $update['product_type'][$site_alias] = $deliveryBand;
                    }
                    $uplift = $siteInfo[2] ?? '';
                    if (!in_array((int)$uplift, (array)$allowedUpliftIds[$site_alias])) {
                        continue;
                    }
                    if ($uplift) {
                        $update['uplift_id'][$site_alias] = $uplift;
                    }
                    fwrite($fileHDL,
                        $product_id.";".$site_alias.";".$deliveryBand.";".$uplift."\n"
                    );
                }
            }
            fclose($fileHDL);

            if ($is_license) {
                $update['license'] = 1;
            }


            //serialize fields
            foreach (['sites', 'product_type', 'uplift_id'] as $fld) {
                if ($fld === 'uplift_id') {
                    foreach ($sites as $site) {
                        $siteInfo = array_map('trim', explode('|', $site));
                        if (isset($siteInfo[0])) {
                            $site_alias = strtolower($siteInfo[0]);
                            if (!$allowedUpliftIds[$site_alias]) {
                                $error_text = __CLASS__ . ": Product ID " . $product_id . " have no Uplift ID for site " . $site_alias;
                                throw new Exception($error_text);
                            }

                            if (!in_array((int)$update['uplift_id'][$site_alias], $allowedUpliftIds[$site_alias])) {
                                unset($update['uplift_id'][$site_alias]);
                            }
                        }
                    }
                }
            }

            //now save sites to product
            Product::find($product_id)?->update($update);
            Registry::cache()->flush('product');
        }

        return ['result' => true];
    }
}
