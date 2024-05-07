<?php

namespace abc\extensions\tims_catalog\modules\workers;

use abc\core\ABC;
use abc\core\engine\Registry;
use abc\core\lib\ALanguageManager;
use abc\core\lib\CategoryApiClient;
use abc\core\lib\ManufacturerApiClient;
use abc\core\lib\ProductApiClient;
use abc\models\catalog\Category;
use abc\models\catalog\Manufacturer;
use abc\models\catalog\Product;
use abc\models\QueryBuilder;
use abc\modules\workers\ABaseWorker;

/**
 * Class SetUuid
 *
 * @package abc\extensions\tims_catalog\modules\workers
 * @property ALanguageManager     $language;
 */
class SetUuid extends ABaseWorker
{
    protected $registry;
    protected $language;
    protected $db;
    /**
     * @var CategoryApiClient
     */
    private $apiSafari;
    /**
     * @var CategoryApiClient
     */
    private $apiUfs;

    /**
     * CategoryExport constructor.
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->registry = Registry::getInstance();
        $this->db = $this->registry->get('db');
        $this->language = $this->registry->get('language');
    }

    public function __get($name)
    {
        return $this->registry->get($name);
    }

    public function getModuleMethods()
    {
        return ['products', 'categories', 'manufacturers'];
    }

    public function postProcessing()
    {
    }

    // php abcexec job:run --worker=setUuid --method=categories --category_id=223
    public function categories()
    {
        $sites = ABC::env('sites');

        $this->apiSafari = new CategoryApiClient($sites['bidfood']['api_url'],
            $sites['bidfood']['api_port'],
            $sites['bidfood']['api_key'],
            $sites['bidfood']['api_username'],
            $sites['bidfood']['api_password']);

        $this->apiUfs = new CategoryApiClient($sites['ufs']['api_url'],
            $sites['ufs']['api_port'],
            $sites['ufs']['api_key'],
            $sites['ufs']['api_username'],
            $sites['ufs']['api_password']);

        $categoryId = func_get_arg(0)['category_id'];

        /** @var QueryBuilder $query */
        $query = Category::whereNull('uuid')->orderBy('parent_id');
        if ($categoryId) {
            $query->where('category_id', '=', $categoryId);
        }
        $arCategories = $query->get();

        if (!$arCategories) {
            return false;
        }

        $language_id = $this->language->getLanguageIdByCode('en');
        Category::setCurrentLanguageID($language_id);

        /**
         * @var Category $category
         */
        foreach ($arCategories as $category) {
            $category->uuid = (string)$category->resolveUuid();
            $category->save();
            $categoryAllData = $category->getAllData();
            $pathTree = Category::getPath($category->category_id, 'id');
            $pathTree = str_replace('text_separator', '_', $pathTree);

            $by = [
                'pathTree'  => $pathTree,
                'update_by' => 'pathTree',
                'get_by'    => 'pathTree',
            ];

            $apiCategory = $this->apiSafari->get($by);

            if (!$apiCategory || (is_array($apiCategory) && isset($apiCategory['error_status']) && $apiCategory['error_status'] === 0)) {
                //$result = $this->apiSafari->create($this->prepareData($categoryAllData));
            } else {
                $this->apiSafari->update($by, $this->prepareData($categoryAllData));
            }

            $apiCategory2 = $this->apiUfs->get($by);
            if (!$apiCategory2 || (is_array($apiCategory2) && isset($apiCategory2['error_status']) && $apiCategory2['error_status'] === 0)) {
                //$result = $this->apiUfs->create($this->prepareData($categoryAllData));
            } else {
                $this->apiUfs->update($by, $this->prepareData($categoryAllData));
            }
        }
    }

    //php abcexec job:run --worker=setUuid --method=manufacturers
    public function manufacturers()
    {
        $sites = ABC::env('sites');

        $this->apiSafari = new ManufacturerApiClient($sites['bidfood']['api_url'],
            $sites['bidfood']['api_port'],
            $sites['bidfood']['api_key'],
            $sites['bidfood']['api_username'],
            $sites['bidfood']['api_password']);

        $this->apiUfs = new ManufacturerApiClient($sites['ufs']['api_url'],
            $sites['ufs']['api_port'],
            $sites['ufs']['api_key'],
            $sites['ufs']['api_username'],
            $sites['ufs']['api_password']);

        $manufacturerId = func_get_arg(0)['manufacturer_id'];
        $arManufacturers = Manufacturer::whereNull('uuid');
        if ($manufacturerId) {
            $arManufacturers = $arManufacturers->where('manufacturer_id', '=', $manufacturerId);
        }
        $arManufacturers = $arManufacturers->get();

        if (!$arManufacturers) {
            return false;
        }

        /**
         * @var Manufacturer $manufacturer
         */
        foreach ($arManufacturers as $manufacturer) {
            $manufacturer->uuid = (string)$manufacturer->resolveUuid();
            $manufacturer->save();
            $manufacturerAllData = $manufacturer->getAllData();

            $by = [
                'name'      => $manufacturer->name,
                'update_by' => 'name',
                'get_by'    => 'name',
            ];

            $apiManufacturer = $this->apiSafari->get($by);

            if (!$apiManufacturer || (is_array($apiManufacturer) && isset($apiManufacturer['error_status']) && $apiManufacturer['error_status'] === 0)) {
                // $this->apiSafari->create($this->prepareData($manufacturerAllData));
            } else {
                $this->apiSafari->update($by, $this->prepareData($manufacturerAllData));
            }

            $apiManufacturer2 = $this->apiUfs->get($by);
            if (!$apiManufacturer2 || (is_array($apiManufacturer2) && isset($apiManufacturer2['error_status']) && $apiManufacturer2['error_status'] === 0)) {
                //   $this->apiUfs->create($this->prepareData($manufacturerAllData));
            } else {
                $this->apiUfs->update($by, $this->prepareData($manufacturerAllData));
            }
        }
        return true;
    }

    //php abcexec job:run --worker=setUuid --method=products
    public function products()
    {
        $sites = ABC::env('sites');

        $this->apiSafari = new ProductApiClient($sites['bidfood']['api_url'],
            $sites['bidfood']['api_port'],
            $sites['bidfood']['api_key'],
            $sites['bidfood']['api_username'],
            $sites['bidfood']['api_password']);

        $this->apiUfs = new ProductApiClient($sites['ufs']['api_url'],
            $sites['ufs']['api_port'],
            $sites['ufs']['api_key'],
            $sites['ufs']['api_username'],
            $sites['ufs']['api_password']);

        $productId = func_get_arg(0)['product_id'];
        $arProducts = Product::whereNull('uuid');
        if ($productId) {
            $arProducts = $arProducts->where('product_id', '=', $productId);
        }
        $arProducts = $arProducts->get();

        if (!$arProducts) {
            return false;
        }

        /**
         * @var Product $product
         */
        foreach ($arProducts as $product) {

            $product->uuid = (string)$product->resolveUuid();
            $product->save();
            $productAllData = [
                'catalog_id' => $product->product_id,
                'uuid'       => $product->uuid,
            ];

            $by = [
                'catalog_id'      => $product->product_id,
                'update_by' => 'catalog_id',
                'get_by'    => 'catalog_id',
            ];

            $sites = $product->sites;


            if (in_array('bidfood', $sites, false)) {

                $apiProduct = $this->apiSafari->get($by);
                if (!$apiProduct || (is_array($apiProduct) && isset($apiProduct['error_status']) && $apiProduct['error_status'] === 0)) {

                } else {
                    $this->apiSafari->update($by, $productAllData);
                }
            }

            if (in_array('ufs', $sites, false)) {
                $apiProduct2 = $this->apiUfs->get($by);
                if (!$apiProduct2 || (is_array($apiProduct2) && isset($apiProduct2['error_status']) && $apiProduct2['error_status'] === 0)) {
                    //   $this->apiUfs->create($this->prepareData($manufacturerAllData));
                } else {
                    $this->apiUfs->update($by, $productAllData);
                }
            }
        }
    }

    protected function prepareData($categoryData)
    {
        if ($categoryData['parent_id']) {
            $category = Category::find($categoryData['parent_id']);
            if ($category) {
                $categoryData['parent_uuid'] = $category->uuid;
            }
        }
        unset($categoryData['parent_id']);

        $categoryData['category_description'] = [];
        foreach ($categoryData['descriptions'] as $description) {
            $categoryData['category_description'][$description['language_id']] = $description;
        }
        unset($categoryData['descriptions']);

        $categoryData['category_store'] = [];
        if (isset($categoryData['stores']) && is_array($categoryData['stores'])) {
            foreach ($categoryData['stores'] as $store) {
                $categoryData['category_store'][] = $store['store_id'];
            }
            unset($categoryData['stores']);
        }

        $categoryData['category_images'] = [];
        if (isset($categoryData['images']) && is_array($categoryData['images'])) {
            foreach ($categoryData['images'] as $key => $image) {
                if ($key !== 'image_main') {
                    foreach ($image as $item) {
                        $categoryData['category_images'][] = $item['direct_url'];
                    }
                }
            }
            unset($categoryData['images']);
        }
        return $categoryData;
    }

}