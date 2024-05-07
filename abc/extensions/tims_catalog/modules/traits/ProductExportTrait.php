<?php

namespace abc\extensions\tims_catalog\modules\traits;

use abc\core\ABC;
use abc\core\engine\Registry;
use abc\core\lib\AError;
use abc\core\lib\AException;
use abc\core\lib\AJson;
use abc\core\lib\ALanguageManager;
use abc\models\catalog\Category;
use abc\models\catalog\Product;
use abc\models\locale\LengthClass;
use abc\models\locale\WeightClass;
use abc\models\system\Setting;
use abc\models\system\TaxClassDescription;
use Carbon\Carbon;
use Exception;
use InvalidArgumentException;
use ReflectionException;

/**
 * Trait ProductExportTrait
 *
 * @package abc\extensions\tims_catalog\modules\traits
 *
 * @property ALanguageManager $language;
 */
trait ProductExportTrait{
    /**
     * @var Registry
     */
    protected $registry;
    protected $site_alias, $language_list = [];
    protected $api_destinations = [];
    protected $excludeFields = [];
    protected $allowedUpliftIds = [];

    public function __get($name)
    {
        return $this->registry->get($name);
    }

    /**
     * @param array $product_ids
     * @param array $expSites
     * @param array $excludeFields
     *
     * @return array
     * @throws ReflectionException
     * @throws AException
     */
    public function processBatch(array $product_ids, array $expSites = [], array $excludeFields = [])
    {
        $this->excludeFields = $excludeFields;
        $failed = $success = [];
        $site_products = [];
        $deleteIds = [];

        if (!$product_ids) {
            $result = false;
        } else {
            //check if products assigned to sites
            $products = Product::whereIn('product_id', $product_ids)->get();
            $this->api_destinations = ABC::env('sites');

            foreach ($products as $row) {
               $site_list = $row->sites ?? [];
               if (!$site_list) {
                   $deleteIds[] = $row->product_id;
                   continue;
               }
               foreach ($this->api_destinations as $site_alias => $info) {
                   if (in_array($site_alias, $site_list)) {
                       $site_products[$site_alias][] = $row->product_id;
                   }
               }
            }

            $uplift_cats = (array)ABC::env('product')['uplift_categories'];
            foreach ($uplift_cats as $key => $cat) {
                foreach ($this->api_destinations as $site_alias => $info) {
                        if ($cat[$site_alias]) {
                            $this->allowedUpliftIds[$site_alias][] = $key;
                    }
                }
            }

            //build language codes list for data preparing
            $languages = $this->language->getAvailableLanguages();
            foreach ($languages as $lang) {
                $this->language_list[$lang['language_id']] = $lang['code'];
            }
            $credentials = [];
            if($site_products) {
                foreach ($site_products as $site_alias => $products) {
                    $this->site_alias = $site_alias;

                    //get token first
                    if (!$credentials[$site_alias]) {
                        $credentials[$site_alias] = [
                            'api_key'  => $this->api_destinations[$site_alias]['api_key'],
                            'api_url'  => $this->api_destinations[$site_alias]['api_url'],
                            'api_port' => $this->api_destinations[$site_alias]['api_port']
                        ];

                        $response = $this->send(
                            $credentials[$site_alias],
                            [
                                'rt'       => 'a/index/login',
                                'username' => $this->api_destinations[$site_alias]['api_username'],
                                'password' => $this->api_destinations[$site_alias]['api_password'],
                                'api_key'  => $this->api_destinations[$site_alias]['api_key'],
                            ]
                        );

                        if ($response['token']) {
                            $credentials[$site_alias]['token'] = $response['token'];
                            $this->toLog('Connected To ' . $site_alias . '. Token: ' . $response['token']);
                        } elseif (!$response['token']) {
                            $message = 'Cannot to get token for export to ' . $site_alias . ': ';
                            $this->toLog($message);
                            $failed[] = $message;
                            continue;
                        } elseif ($response['error_text']) {
                            $message = 'Try to export to '
                                . $site_alias . ': '
                                . $response['error_title'] . " " . $response['error_text'];
                            $this->toLog($message);
                            $failed[] = $message;
                            continue;
                        }
                    }

                    foreach ($products as $product_id) {
                        if ($credentials[$site_alias]['token']) {
                            $error_msg = '';
                            $this->toLog('Try to export product ID ' . $product_id);

                            $extraData = [];
                            if (isset($expSites[$product_id][$site_alias])) {
                                $extraData = $expSites[$product_id][$site_alias];
                            }

                            $put_result = $this->putProduct($credentials[$site_alias], $product_id, $extraData);
                            if (isset($put_result['Error'])) {
                                $error_msg = $put_result['Error'];
                                $put_result = false;
                                $this->toLog(
                                    'Export of product ID '.$product_id.' to "'.$site_alias.'" failed! '
                                    .$error_msg
                                );
                            }

                            if ($put_result) {
                                $success[] = $product_id;
                                $this->toLog('Export of product ID '.$product_id.' to "'.$site_alias.'" - success! ');
                            } else {
                                $failed[$product_id] = $error_msg;
                            }
                        }
                    }
                }
                $result = ($failed || !$success) ? false : true;
            }else{
                $result = true;
            }
        }


        if ($result) {
            $this->deleteBatch($deleteIds, array_keys((array)$site_products));
            $output = [
                'result'  => true,
                'message' => $success ? count($success) : count($product_ids) . ' product(s) have been successfully synced',
            ];
        } else {
            $output = [
                'result'     => false,
                'error_text' => 'Export failed. '.implode('; ', $failed),
            ];
        }
        $this->toLog('Export of product batch. '.$output['message'].$output['error_text']);
        return $output;
    }

    /**
     * @param array $options
     * @param int $product_id
     * @param array $data
     *
     * @return array|false
     * @throws ReflectionException
     * @throws AException
     */
    protected function putProduct($options, $product_id, $data = [])
    {
        if (!$options['token'] || !$product_id) {
            return false;
        }

        $all_data = [];
        /** @var Product $product */
        $product = Product::find($product_id);

        if ($product) {
            $all_data = $product->getAllData();
            $all_data = $this->prepareData($all_data);
        }
        if (!$all_data) {
            return false;
        }

        if(isset($all_data['product_type']) && $all_data['product_type'][$this->site_alias]){
            $all_data['product_type'] = $all_data['product_type'][$this->site_alias];
        }else{
            unset($all_data['product_type']);
        }
        if(isset($all_data['uplift_id']) && $all_data['uplift_id'][$this->site_alias] &&
        in_array($all_data['uplift_id'][$this->site_alias], $this->allowedUpliftIds[$this->site_alias])){
            $all_data['uplift_id'] = $all_data['uplift_id'][$this->site_alias];
        }else{
            unset($all_data['uplift_id']);
            return ['Error' => 'Product have incorrect UpliftId. Export not allowed'];
        }

        $all_data['sites'] = $all_data['sites'] ?? [];
        //no sites to update, skip
        if (!in_array($this->site_alias, $all_data['sites'])) {
            return false;
        }
        unset($all_data['sites']);

        $api_data = [
            'rt'        => 'a/catalog/product',
            'token'     => $options['token'],
            'api_key'   => $options['api_key'],
            'update_by' => 'catalog_id',
        ];

        foreach ($all_data as $k => $v) {
            if (in_array($k, ["images", "categories", "stores"])) {
                continue;
            }
            if ($k == 'product_id') {
                $k = 'catalog_id';
            }
            $api_data[$k] = $v;
        }

        $protocolSetting = Setting::select('value')->where('key', '=', 'protocol_url')->first();
        $protocol = 'http';
        if ($protocolSetting) {
            $protocol = $protocolSetting->value;
        }

        //product_images
        foreach ($all_data['images']['images'] as $image) {
            $api_data['images'][] = !str_starts_with($image['direct_url'], $protocol) ? $protocol . ':' . $image['direct_url'] : $image['direct_url'];
        }

        //options images
        foreach ((array)$all_data['options'] as $opk => $option) {
            foreach ((array)$option['values'] as $opkv => $option_value) {
                foreach ((array)$option_value['images']['images'] as $imgk => $image) {
                    $api_data['options'][$opk]['values'][$opkv]['images'][$imgk] =
                        !str_starts_with($image['direct_url'], $protocol) ? $protocol . ':' . $image['direct_url'] : $image['direct_url'];
                }
                unset($api_data['options'][$opk]['values'][$opkv]['images']['images']);
            }
        }

        if (is_array($all_data['categories'])) {
            //even category list is empty - send empty uuids list to detach product on node
            $uuids = (array)array_column($all_data['categories'], 'uuid');
            $api_data['category_uuids'] = $uuids;
        }

        if (is_array($all_data['stores'])) {
            $api_data['stores'] = array_column($all_data['stores'], 'store_id');
        }

        $ifExists = $this->send(
            [
                'request_type' => 'get',
                'api_url'    => $options['api_url'],
                'api_port'    => $options['api_port']
            ],
            [
                'rt'         => 'a/catalog/product',
                'token'      => $options['token'],
                'api_key'    => $options['api_key'],
                'get_by'     => 'catalog_id',
                'catalog_id' => $product_id,
            ]
        );


        //if product absent - insert
        if (!isset($ifExists['product_id'])) {
            unset($api_data['product_id'], $api_data['update_by']);
        }
        //if status enabled on catalog - do not send it to remote sites
        //On remotes site status will be untouchable
        //disabled by Gaurav request
//        elseif ($api_data['status']) {
//            unset($api_data['status']);
//        }

        //add override data (site specific)
        foreach ($data as $key => $value) {
            $api_data[$key] = $value;
        }

        $result = $this->send($options, $api_data);
        if(!$result){
            $result['Error'] = 'No response from remote host!';
        }
        return $result;
    }


    public function deleteBatch($product_ids, array $excludeSites = []){
        $product_ids = array_values($product_ids);
        $this->api_destinations = (array)ABC::env('sites');
        $failed = $success = [];
        if (!$product_ids) {
            return [
                'result'     => true,
                'error_text' => 'Empty product IDs was set.',
            ];
        } elseif ($this->api_destinations) {
            //check if products assigned to sites
            $products = Product::whereIn('product_id', $product_ids)->get()->toArray();
            $site_products = [];
            foreach ($products as $row) {
                if (!$excludeSites) {
                    $site_list = array_keys($this->api_destinations);
                } else {
                    $site_list = array_diff(array_keys($this->api_destinations), $excludeSites);
                }
                if (!$site_list) {
                   unset($product_ids[array_search($row['product_id'], $product_ids)]);
                   continue;
               }

               foreach ($this->api_destinations as $site_alias => $info) {
                   if (in_array($site_alias, $site_list)) {
                       $site_products[$site_alias][] = $row['product_id'];
                   }
               }
            }
            //build language codes list for data preparing
            $languages = $this->language->getAvailableLanguages();
            foreach ($languages as $lang) {
                $this->language_list[$lang['language_id']] = $lang['code'];
            }
            $credentials = [];
            foreach ($site_products as $site_alias => $products) {
                foreach ($products as $product_id) {
                    $this->site_alias = $site_alias;

                    //get token first
                    if (!$credentials[$site_alias]) {
                        $credentials[$site_alias] = [
                            'api_key'  => $this->api_destinations[$site_alias]['api_key'],
                            'api_url'  => $this->api_destinations[$site_alias]['api_url'],
                            'api_port' => $this->api_destinations[$site_alias]['api_port']
                        ];

                        $response = $this->send(
                            $credentials[$site_alias],
                            [
                                'rt'       => 'a/index/login',
                                'username' => $this->api_destinations[$site_alias]['api_username'],
                                'password' => $this->api_destinations[$site_alias]['api_password'],
                                'api_key'  => $this->api_destinations[$site_alias]['api_key'],
                            ]
                        );

                        if ($response['token']) {
                            $credentials[$site_alias]['token'] = $response['token'];
                            $this->toLog('Connected To '.$site_alias.'. Token: '.$response['token']);
                        } elseif ($response['error_text']) {
                            $message = 'Try to delete on '
                                .$site_alias.': '
                                .$response['error_title']." ".$response['error_text'];
                            $this->toLog($message);
                            $failed[] = $message;

                            continue;
                        }

                    }

                    if ($credentials[$site_alias]['token']) {
                        $error_msg = '';
                        $this->toLog('Try to delete product ID '.$product_id);

                        $delete_result = $this->deleteProduct($credentials[$site_alias], $product_id);
                        if (isset($delete_result['Error'])) {
                            $error_msg = $delete_result['Error'];
                            $delete_result = false;
                            $this->toLog(
                                'Deleting of product ID '.$product_id.' to "'.$site_alias.'" failed! '
                                .$error_msg
                            );
                        }

                        if ($delete_result) {
                            $success[] = $product_id;
                            $this->toLog('Deleting of product ID ' . $product_id . ' to "' . $site_alias . '" - success! ');
                        } else {
                            $failed[$product_id] = $error_msg;
                        }
                    }
                }
            }
            $result = !($failed || !$success);
        } else {
            //in case when nodes not found
            $result = true;
        }

        if ($result) {
            $output = [
                'result'  => true,
                'message' => sizeof($success).' product(s) have been successfully deleted',
            ];
        } else {
            $output = [
                'result'     => false,
                'error_text' => 'Remote deleting failed. '.implode('; ', $failed),
            ];
        }
        $this->toLog('Deleting of product batch. '.$output['message'].$output['error_text']);
        return $output;
    }

    /**
     * @param array $options
     * @param int $product_id
     *
     * @return array|false
     * @throws ReflectionException
     */
    protected function deleteProduct($options, $product_id)
    {
        if (!$options['token'] || !$product_id) {
            return false;
        }

        $api_data = [
            'rt'        => 'a/catalog/product',
            'token'     => $options['token'],
            'api_key'   => $options['api_key'],
            'operation' => 'delete',
            'product_id'=> $product_id
        ];

        $result = $this->send($options, $api_data);
        if(!$result){
            $result['Error'] = 'No response from remote host!';
        }
        return $result;
    }

    /**
     * @param $list
     * @param $language_id
     * @param int $level
     *
     * @return mixed
     * @throws Exception
     */
    protected function buildPathArray($list,  $language_id, $level = 0){
        $language_id = (int)$language_id;
        if(!$language_id){
            return [];
        }
        Category::setCurrentLanguageID($language_id);
        $output = Category::getCategoryInfo($list[$level],$language_id);
        if($level< count($list)-1){
            $output['children'] = $this->buildPathArray($list, $language_id, $level+1);
        }
        return $output;
    }

    /**
     * @param array $options
     * @param array $api_data
     *
     * @return bool|null
     * @throws ReflectionException
     */
    protected function send($options, $api_data = [])
    {
        $isPost = $options['request_type'] === 'get' ? false : true;
        $api_url = $options['api_url'];
        if (!$isPost) {
            $api_url .= (is_int(strpos($options['api_url'], '?')) ? '&' : '?');
            $api_url .= http_build_query($api_data);
        }

        $curl = curl_init($api_url);
        curl_setopt($curl, CURLOPT_PORT, $options['api_port']);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['X-App-Api-Key: ' . $api_data['api_key']]);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);
        if ($isPost) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($api_data));
        }

        $response = curl_exec($curl);
        if (!$response) {
            $err = new AError(
                'Tims Catalog Sync failed: '
                . curl_error($curl) . '(' . curl_errno($curl) . ')'
                . "\n\nOptions: "
                . var_export($options, true) . "\n\nSent Data: " . var_export($api_data, true)
            );
            $err->toLog()->toDebug();
            curl_close($curl);
            return false;
        } else {
            $response_data = AJson::decode($response, true);
            curl_close($curl);
            return $response_data;
        }
    }

    /**
     * @param array|string $data
     *
     * @return array|string
     */
    protected function prepareData($data)
    {
        if (isset($data['featured'])) {
            $data['featured'] = (int)$data['featured'];
        }

        if (isset($data['length_class_id'])) {
            $data['length_class_iso_code'] = LengthClass::find($data['length_class_id'])->iso_code;
        }
        if (isset($data['weight_class_id'])) {
            $data['weight_class_iso_code'] = WeightClass::find($data['weight_class_id'])->iso_code;
        }
        if (isset($data['tax_class_id'])) {
            $taxClassDescriptions = TaxClassDescription::where('tax_class_id', $data['tax_class_id'])
                ->get()
                ?->toArray();
            if ($taxClassDescriptions) {
                $data['tax_class_descriptions'] = [];
                foreach ($taxClassDescriptions as $tClass) {
                    $tClass['language_code'] = $this->language_list[$tClass['language_id']];
                    unset(
                        $tClass['tax_class_id'],
                        $tClass['date_added'],
                        $tClass['date_modified'],
                    );
                    $data['tax_class']['descriptions'][] = $tClass;
                }
            }
        }

        //remove incorrect dates
        foreach (self::DATE_FIELDS as $dateField) {
            if (isset($data[$dateField])) {
                try {
                    Carbon::createFromFormat('Y-m-d H:i:s', $data[$dateField]);
                } catch (InvalidArgumentException $e) {
                    unset($data[$dateField]);
                }
            }
        }

        if (is_array($data) && array_key_exists('date_deleted', $data) && $data['date_deleted'] === null){
            unset($data['date_deleted']);
        }

        if (is_array($data)) {
            foreach (array_keys($data) as $key) {
                if (in_array($key, $this->excludeFields, true)) {
                    unset($data[$key]);
                }
            }
        }

        if (!is_array($data)) {
            return html_entity_decode($data, ENT_QUOTES, ABC::env('APP_CHARSET'));
        } else {
            if (isset($data['language_id'])) {
                $data['language_code'] = $this->language_list[$data['language_id']];
            }
            foreach ($data as &$v) {
                $v = $this->prepareData($v);
            }
            return $data;
        }
    }
}