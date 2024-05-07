<?php

namespace abc\extensions\tims_catalog\modules\workers;

use abc\core\ABC;
use abc\core\lib\ManufacturerApiClient;
use abc\models\catalog\Manufacturer;
use abc\modules\workers\ABaseWorker;

class ManufacturerExport extends ABaseWorker
{
    private $apis = [];
    public function __construct()
    {
        parent::__construct();

        $this->reRunIfFailed = true;
        /** @see ../../config/uat/sites.php */
        $sites = ABC::env('sites');
        if (!$sites) {
            throw new \Exception('Site list not found in ABC::env() (see config/sites.php)', 1000);
        }

        foreach ($sites as $siteAlias => $cfg) {
            $this->apis[$siteAlias] = new ManufacturerApiClient(
                $cfg['api_url'],
                $cfg['api_port'],
                $cfg['api_key'],
                $cfg['api_username'],
                $cfg['api_password']
            );
            $this->apis[$siteAlias]->requestToken();
            //check if login was successful
            if(!$this->apis[$siteAlias]->getToken()){
                throw new \Exception('Cannot to login to API of ' . $siteAlias);
            }
        }
    }

    public function getModuleMethods()
    {
        return ['export', 'delete'];
    }

    public function postProcessing()
    {
    }

    // php abcexec job:run --worker=manufacturerExport --method=export --manufacturer_id=223
    public function export()
    {
        $manufacturerId = func_get_arg(0)['manufacturer_id'];

        if (!$manufacturerId) {
            return false;
        }

        $manufacturerData = Manufacturer::find($manufacturerId);
        if (!$manufacturerData) {
            return false;
        }
        $manufacturerAllData = $this->prepareData($manufacturerData->getAllData());

        $by = [
            'uuid'      => $manufacturerData->uuid,
            'get_by'    => 'uuid',
            'update_by' => 'uuid',
            'delete_by' => 'uuid',
        ];

        foreach ($this->apis as $apiClient) {
            /** @var ManufacturerApiClient $apiClient */
            $apiManufacturer = $apiClient->get($by);

            if (!$apiManufacturer
                || (is_array($apiManufacturer) && $apiManufacturer['error_status'] === 0)
            ) {
                $apiClient->create($manufacturerAllData);
            } else {
                $apiClient->update($by, $manufacturerAllData);
            }
        }

        return [
            'result'  => true,
            'message' => 'Manufacturer ID '.$manufacturerId.' successfully exported',
        ];
    }

    // php abcexec job:run --worker=manufacturerExport --method=delete --uuid=223
    public function delete()
    {
        $uuid = func_get_arg(0)['uuid'];

        if (!$uuid) {
            return false;
        }

        $by = [
            'uuid'      => $uuid,
            'get_by'    => 'uuid',
            'update_by' => 'uuid',
            'delete_by' => 'uuid',
        ];

        foreach ($this->apis as $apiClient) {
            $apiClient->delete($by);
        }

        return [
            'result'  => true,
            'message' => 'Manufacturer ID '.$uuid.' successfully deleted',
        ];
    }

    private function prepareData($manufacturerData)
    {
        $manufacturerData['manufacturer_store'] = [];
        if (isset($manufacturerData['stores']) && is_array($manufacturerData['stores'])) {
            $manufacturerData['manufacturer_store'] = array_column($manufacturerData['stores'], 'store_id');
            unset($manufacturerData['stores']);
        }

        $manufacturerData['manufacturer_images'] = [];
        if (isset($manufacturerData['images']) && is_array($manufacturerData['images'])) {
            foreach ($manufacturerData['images'] as $key => $image) {
                if ($key !== 'image_main') {
                    $manufacturerData['manufacturer_images'] = array_column($image, 'direct_url');
                }
            }
            unset($manufacturerData['images']);
        }
        return $manufacturerData;
    }
}