<?php

namespace abc\extensions\tims_catalog\modules\workers;

use abc\core\ABC;
use abc\core\engine\Registry;
use abc\core\lib\CategoryApiClient;
use abc\models\catalog\Category;
use abc\modules\workers\ABaseWorker;
use Exception;

class CategoryExport extends ABaseWorker
{
    private $apis = [];

    /**
     * CategoryExport constructor.
     *
     */
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
            $this->apis[$siteAlias] = new CategoryApiClient(
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
        return ['export', 'exportAll', 'delete'];
    }

    public function postProcessing()
    {
    }

    // php abcexec job:run --worker=categoryExport --method=export --category_id=223
    public function export()
    {
        $categoryId = func_get_arg(0)['category_id'];

        if (!$categoryId) {
            return false;
        }

        $categoryData = Category::find($categoryId);
        if (!$categoryData) {
            return false;
        }
        $categoryAllData = $this->prepareData($categoryData->getAllData());

        //sync the parents
        $path = explode('_', $categoryAllData['path']);
        $parentIds = array_filter($path, function ($val) use ($categoryId) {
            return $val != $categoryId;
        });

        if ($parentIds) {
            foreach ($parentIds as $parentId) {
                $result = $this->export(['category_id' => $parentId]);
                if (!$result) {
                    throw new Exception('Export Category Error. Parent Category ID ' . $parentId . ' export failed!');
                }
            }
        }

        $by = [
            'uuid'      => $categoryData->uuid,
            'get_by'    => 'uuid',
            'update_by' => 'uuid',
            'delete_by' => 'uuid',
        ];

        foreach ($this->apis as $apiClient) {
            /** @var CategoryApiClient $apiClient */
            $apiCategory = $apiClient->get($by);

            if (!$apiCategory
                || (is_array($apiCategory) && $apiCategory['error_status'] === 0)
            ) {
                $apiClient->create($categoryAllData);
            } else {
                $apiClient->update($by, $categoryAllData);
            }
        }

        return [
            'result'  => true,
            'message' => 'Category ID ' . $categoryId . ' successfully exported',
        ];

    }

    // php abcexec job:run --worker=categoryExport --method=exportAll
    public function exportAll($ids = [])
    {

        $categories = $ids
            ? Category::whereIn('category_id', $ids)
            : Category::all();

        $output = [
            'result'  => true,
            'message' => ''
        ];
        foreach ($categories as $c) {
            $res = $this->export(['category_id' => $c->category_id]);
            if (!$res || $res['result'] === false) {
                $output['result'] = false;
            }
            $output['message'] .= $res['message'];
        }
        return $output;
    }

    // php abcexec job:run --worker=categoryExport --method=delete --uuid='sadasaas-asd--asd'
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
            'message' => 'Category ID ' . $uuid . ' successfully deleted',
        ];
    }

    private function prepareData($categoryData)
    {
        if ($categoryData['parent_id']) {
            $category = Category::find($categoryData['parent_id']);
            if ($category) {
                $categoryData['parent_uuid'] = $category->uuid;
            } else {
                Registry::log()->error(
                    'Parent Category ID "' . $categoryData['parent_id']
                    . '" not found in the database during export to node!'
                    . "\n Input Data: " . var_export($categoryData, true)
                    . "(see " . __FILE__ . " " . __FUNCTION__ . ")"
                );
            }
        }
        unset(
            $categoryData['parent_id'],
            $categoryData['category_id']
        );

        $categoryData['category_description'] = [];
        foreach ($categoryData['descriptions'] as $description) {
            if (isset($description['category_id'])) {
                unset($description['category_id']);
            }
            $categoryData['category_description'][$description['language_id']] = $description;
        }
        unset($categoryData['descriptions']);

        $categoryData['category_store'] = [];
        if (isset($categoryData['stores']) && is_array($categoryData['stores'])) {
            $categoryData['category_store'] = array_column($categoryData['stores'], 'store_id');
            unset($categoryData['stores']);
        }

        $categoryData['category_images'] = [];
        if (is_array($categoryData['images'])) {
            foreach ($categoryData['images'] as $key => $image) {
                if ($key !== 'image_main') {
                    $categoryData['category_images'] = array_column($image, 'direct_url');
                }
            }
            unset($categoryData['images']);
        }
        return $categoryData;
    }
}