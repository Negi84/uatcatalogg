<?php

namespace abc\core\extension;

use abc\controllers\admin\ControllerPagesCatalogManufacturer;
use abc\core\ABC;
use abc\core\engine\ADispatcher;
use abc\core\engine\AForm;
use abc\core\engine\AHtml;
use abc\core\engine\ALanguage;
use abc\core\engine\Extension;
use abc\core\engine\Registry;
use abc\core\lib\ADB;
use abc\core\lib\AError;
use abc\core\lib\AJson;
use abc\core\lib\ALanguageManager;
use abc\models\admin\ModelCatalogProduct;
use abc\models\admin\ModelToolImportProcess;
use abc\models\catalog\Category;
use abc\models\catalog\Manufacturer;
use abc\models\catalog\Product;
use abc\models\catalog\ProductOptionValue;
use abc\models\locale\Country;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Query\JoinClause;
use ReflectionException;

/**
 * Class ExtensionTims
 * @property ADB $db
 * @property AHtml $html
 * @property ALanguage|ALanguageManager $language
 */
class ExtensionTimsCatalog extends Extension
{
    protected $registry;
    protected $productRequiredFields = [
        'sku',
        'cost',
    ];

    public function __construct()
    {
        $this->registry = Registry::getInstance();
    }

    public function __get($name)
    {
        return $this->registry->get($name);
    }

    public function onControllerCommonHead_InitData() {
        $that = $this->baseObject;
        $that->document->addStyle(
            [
                'href'  => $that->view->templateResource('assets/css/tims_catalog.css'),
                'rel'   => 'stylesheet',
                'media' => 'screen',
            ]
        );
    }

    /**
     * @return void
     * @deprecated
     */
    public function onModelCatalogProduct_InitData()
    {
        $that = $this->baseObject;
        if (in_array($this->baseObject_method, ['addProduct', 'updateProduct'])) {
            array_push($that->data['perform_json'], 'display_location');
            array_push($that->data['perform_serialize'], 'sites');
        }
    }

    /**
     * @param $method
     * @param $query
     * @param $inData
     * @return void
     *
     * @see Product::getProducts()
     */
    public function onProduct_extendQuery($method, $query, $inData = [])
    {
        if ($method == 'getProducts') {
            //GRID SEARCH
            $jqGridFilters = json_decode(Registry::request()->post['filters'], true);

            if ($jqGridFilters['rules'][0]['field'] == 'supplier') {
                $query->where("products.supplier", 'like', $this->db->escape($jqGridFilters['rules'][0]['data']) . '%');
            }
        }
    }


    public function onControllerPagesCatalogProduct_UpdateData()
    {

        $that = $this->baseObject;

        if (in_array($this->baseObject_method, ['update', 'insert'])) {
            $error =& $that->view->getData('error');
            $error = $error ?? [];
            $that->view->assign('error', $error);

            $product_id = (int)$that->request->get['product_id'];
            $product_info = [];
            if ($product_id) {
                $product = Product::find($product_id);
                $product_info = $product?->toArray();
            }

            //delivery bands
            $options = ['' => 'Please select'];
            if(isset($that->request->post['product_type'])){
                $current_types = $that->request->post['product_type'];
                foreach (array_keys((array)ABC::env('sites')) as $site_alias) {
                    if (!$current_types[$site_alias]) {
                        $options[''] = 'Please select';
                        break;
                    }
                }
            }

            $product_sites = $that->request->post['sites'] ?: $product_info['sites'];
            $product_types = (array)ABC::env('product')['postal_charges'];
            foreach ($product_types as $id => $type) {
                $options[$id] = $type['desc'];
            }
            //when product is license - show only license bands
            if ($product_info['license']) {
                $options = array_intersect_key($options, array_flip(ABC::env('product')['licensed_product_types']));
            }

            $empty_value = false;
            if($product_id) {
                $attr = $product_info['license'] && sizeof($options) == 1 ? 'readonly' : '';
                $product_type = '';
                foreach (array_keys((array)ABC::env('sites')) as $site_alias) {
                    $product_type .= '<div class="input-group afield">';
                    $product_type .= '<div class="input-group col-sm-2">' . $site_alias . ': &nbsp;</div>';
                    $product_type .= '<div class="input-group col-sm-10">';
                    $value = $that->request->post['product_type'][$site_alias]
                        ?? (is_array($product_info['product_type']) ? $product_info['product_type'][$site_alias] : null);
                    $empty_value = !$value && in_array($site_alias, (array)$product_sites)
                        ? true
                        : $empty_value;
                    $product_type .= $that->html->buildElement(
                            [
                            'type'     => 'selectbox',
                            'name'     => 'product_type['.$site_alias.']',
                            'options'  => $options,
                            'value'    => $value,
                            'required' => true,
                            'attr'     => $attr,
                        ]
                    ).'</div>
                    </div>';
                }
            }else{
                //when insert - block other type except e-vouchers
                $value = 'E';
                $attr = 'readonly';

                $product_type = '';
                foreach (array_keys((array)ABC::env('sites')) as $site_alias) {
                    $product_type .= $that->html->buildElement(
                        [
                            'type'  => 'hidden',
                            'name'  => 'product_type[' . $site_alias . ']',
                            'value' => $value,
                        ]
                    );
                }
                $product_type .= '<div class="input-group afield col-sm-7 col-xs-12 mt5">'.$options['E'].'</div>';
            }

            if ( $attr != 'readonly' && $empty_value) {
                $error['product_type'] = 'Please Set Required Field';
            }

            $data = $that->view->getData('form');
            foreach ($data['fields']['data'] as $key => $val) {
                if (in_array($key, ABC::env('remove_product_fields'))) {
                    unset($data['fields']['data'][$key]);
                }
            }

            //sites list for export to
            $value = $that->request->post['sites'] ?? $product_info['sites'];
            $value = !$value ? [] : array_filter((array)$value);
            $value = array_combine($value, $value);

            $options = [];
            foreach (ABC::env('sites') as $alias => $info) {
                $options[$alias] = $alias;
            }

            $sites = $that->html->buildElement(
                [
                    'type'    => 'checkboxgroup',
                    'name'    => 'sites[]',
                    'options' => $options,
                    'value'   => $value,
                    'style'   => 'chosen',
                    'attr'    => ' reload_on_save="true"'
                ]
            );

            $value = $that->request->post['catalog_only'] ?? $product_info['catalog_only'];

            $catalog_only = $that->html->buildElement([
                'type'  => 'checkbox',
                'name'  => 'catalog_only',
                'value' => $value,
                'style' => 'btn_switch btn-group-sm',
            ]);

            $value = $that->request->post['external_url'] ?? $product_info['external_url'];

            $disabled = 'disabled';
            if ($product_info['catalog_only'] == 1) {
                $disabled = '';
            }
            $external_url = $that->html->buildElement([
                'type'    => 'input',
                'name'    => 'external_url',
                'value'   => $value,
                'style'   => 'chosen',
                'attr'    => $disabled.' reload_on_save="true"'
            ]);

            $value = isset($that->request->post['display_location'])
                ? json_decode($that->request->post['display_location'], true)
                : json_decode($product_info['display_location'], true);
            $value = !$value ? [] : array_filter($value);
            $value = array_combine($value, $value);
            $options = [];
            $countries = Country::with('description')->get()->toArray();

            foreach ($countries as $country) {
                $options[$country['iso_code_3']] = $country['description']['name'];
            }

            $display_location = $that->html->buildElement(
                [
                    'type'    => 'checkboxgroup',
                    'name'    => 'display_location[]',
                    'options' => $options,
                    'value'   => $value,
                    'style'   => 'chosen',
                    'attr'    => ' reload_on_save="true"'
                ]
            );
            //reordered by TIMSS-148
            $sku = $data['fields']['data']['sku'];
            unset($data['fields']['data']['sku']);
            //removed by TIMSS-148
            $data['fields']['data']['model'] = $that->html->buildElement([
                            'type'    => 'input',
                            'name'    => 'nominal_code',
                            'value'   => $that->request->post['nominal_code'] ?: $product_info['nominal_code'],
                        ]);
            $that->view->assign('entry_model', 'Nominal Code');

            $supplier = $that->html->buildElement([
                'type'  => 'input',
                'name'  => 'supplier',
                'value' => $that->request->post['supplier'] ?: $product_info['supplier'],
            ]);
            $that->view->assign('entry_supplier', 'Supplier');

            //uplift ID
            $uplift_cats = (array)ABC::env('product')['uplift_categories'];
            $options = [];
            foreach ($uplift_cats as $id => $up_cat) {
                foreach (array_keys((array)ABC::env('sites')) as $site_alias) {
                    if ($up_cat[$site_alias]) {
                        $options[$site_alias][$id] = $up_cat['desc'];
                    }
                }
            }

            foreach (array_keys((array)ABC::env('sites')) as $site_alias) {
                $options[$site_alias] = ['' => '--- Please select ---'] + (array)$options[$site_alias];
            }

            $uplift = '';
            $empty_value = false;

            foreach (array_keys((array)ABC::env('sites')) as $site_alias) {
                $uplift .= '<div class="input-group afield">';
                $uplift .= '<div class="input-group col-sm-2">' . $site_alias . ': &nbsp;</div>';
                $uplift .= '<div class="input-group col-sm-10">';

                $value = $that->request->post['uplift_id'][$site_alias]
                    ?? ($product_info['uplift_id'] ? $product_info['uplift_id'][$site_alias] : '');
                $empty_value = !$value && in_array($site_alias, (array)$product_sites) ? true : $empty_value;
                $uplift .= $that->html->buildElement(
                        [
                            'type' => 'selectbox',
                        'name'     => 'uplift_id['.$site_alias.']',
                        'options'  => $options[$site_alias],
                        'value'    => $value,
                        'required' => true,
                    ]
                ).'</div>
                </div>';
            }
            if ( $empty_value) {
                $error['uplift_id'] = 'Please Set Required Field';
            }

            $that->view->assign('entry_uplift_id', 'Uplift Category');

            $data['fields']['data'] = array_slice($data['fields']['data'], 0, 2, true) +
                [
                    "supplier"     => $supplier,
                    "uplift_id"    => $uplift
                ]
            + array_slice($data['fields']['data'], 2, count($data['fields']['data']) - 1, true);

            //push switch after featured field
            $data['fields']['general'] = array_slice($data['fields']['general'], 0, 2, true) +
                [
                    "product_type" => $product_type,
                    "catalog_only" => $catalog_only,
                    "external_url" => $external_url,
                    "sites"        => $sites,
                    "display_location" => $display_location,
                    "sku" => $sku
                ] +
                array_slice($data['fields']['general'], 2, count($data['fields']['general']) - 1, true);

            //add cost_to_business
            $value = $that->request->post['cost_to_business'] ?? $product_info['cost_to_business'];

            $cost_to_business = $that->html->buildElement(
                [
                    'type'    => 'input',
                    'name'    => 'cost_to_business',
                    'value'   => $value
                ]
            );

            //push switch after featured field
            $data['fields']['data'] = array_slice($data['fields']['data'], 0, 4, true) +
                [
                    "cost_to_business" => $cost_to_business,
                ] +
                array_slice($data['fields']['data'], 4, count($data['fields']['data']) - 1, true);

            foreach ($data['fields'] as &$fields) {
                foreach ($fields as &$fld) {

                    if (in_array($fld->name, $this->productRequiredFields)) {
                        $fld->required = true;
                    }
                    if ($fld->name == 'price') {
                        $fld->attr .= ' readonly    ';
                    }

                    if ($fld->name == 'subtract') {
                        $fld = $that->html->buildElement(
                            [
                                'type'     => 'selectbox',
                                'name'     => 'subtract',
                                'options' => ['0' => $that->language->get('text_no')],
                                'value'   => 0,
                                'attr' => 'readonly',
                            ]
                        );
                    }
                }
            }

            $that->view->assign('entry_product_type', $that->language->get('tims_entry_product_type'));
            $that->view->assign('entry_sites', $that->language->get('tims_entry_sites'));
            $that->view->assign('entry_cost_to_business', $that->language->get('tims_entry_cost_to_business'));
            $that->view->assign('entry_external_url', $that->language->get('tims_entry_external_url'));
            $that->view->assign('entry_catalog_only', $that->language->get('tims_entry_catalog_only'));
            $that->view->assign('entry_display_location', $that->language->get('tims_entry_display_location'));

            $that->view->assign('form', $data);
            if($data['fields']['general']['catalog_only']->value == 1){

                foreach(['weight', 'weight_class'] as $name){
                    unset($error[$name]);
                }
                $that->view->assign('error', $error);
            }

            $that->view->assign('error', $error);

            if ($this->baseObject_method == 'update') {
                $that->view->assign('product_id', $that->request->get['product_id']);
                if (!$product_info['sites']) {
                    $that->view->assign('disable_sync_button', true);
                }
                $that->view->addHookVar('common_content_buttons', $that->view->fetch('common/sync_button.tpl'));
            }
        }
    }


    public function onControllerPagesCatalogProduct_ProcessData()
    {
        $that = $this->baseObject;
        $product_id = $that->data['product_id'];
        if (!$product_id) {
            return null;
        }

        $this->exportProductToSites($product_id);
    }

    public function onControllerPagesCatalogProductOptions_ProcessData()
    {
        $that = $this->baseObject;
        $product_id = $that->data['product_id'];
        if (!$product_id) {
            return null;
        }
        $this->exportProductToSites($product_id);
    }


    public function onControllerResponsesProductProduct_UpdateData()
    {

        if( !in_array($this->baseObject_method, ['update_option', 'del_option', 'update_option_values'])){
            return;
        }

        $that = $this->baseObject;
        $product_id = $that->request->get['product_id'];
        if (!$product_id) {
            return null;
        }
        $this->exportProductToSites($product_id);
    }


    public function onControllerResponsesListingGridProduct_UpdateData()
    {
        $that = $this->baseObject;
        if ($this->baseObject_method != 'main') {
          return;
        }
        if($that->data['response']->rows) {
            $rawData = $that->data['results'];
            foreach ($that->data['response']->rows as $k => $row) {

                $that->data['response']->rows[$k]['cell'][2] = $rawData[$k]['sku'];
                $that->data['response']->rows[$k]['cell'][3] = $rawData[$k]['supplier'];
                $that->data['response']->rows[$k]['cell'][4] = round((float)$rawData[$k]['cost_to_business'], 2);
                $that->data['response']->rows[$k]['cell'][5] = round((float)$rawData[$k]['cost'], 2);
                $that->data['response']->rows[$k]['cell'][6] = $this->html->buildCheckbox(
                    [
                        'name'  => 'status[' . $rawData[$k]['product_id'] . ']',
                        'value' => $rawData[$k]['status'],
                        'style' => 'btn_switch',
                    ]
                );
            }
        }
    }

    //delete product on remote hosts
    public function onControllerResponsesListingGridProduct_ProcessData()
    {
        if (in_array($this->baseObject_method, ['update_field', 'update_relations_field'])) {
            $product_id = (int)func_get_arg(1)['product_id'];
            $this->exportProductToSites($product_id);
            return;
        }

        if ($this->baseObject_method === 'update') {
            $product_id = (int)func_get_arg(1)['product_id'];
            $this->exportProductToSites($product_id);
            return;
        }

        //check point
        if ($this->baseObject_method != 'deleting') {
            return;
        }

        $products = (array)func_get_arg(1);
        $dd = new ADispatcher(
            'task/extension/tims_catalog/delete',
            [
                'task_id' => 0,
                'step_id' => 0,
                'settings' => [
                    'products'=> $products
                ]
            ]
        );

        $json = $dd->dispatchGetOutput('task/extension/tims_catalog/delete');
        $output = AJson::decode($json, true);
        //interrupt if failed
        if($output['result'] === false){
            $error = new AError('');
            $error->toJSONResponse(
                'APPLICATION_ERROR_406',
                [
                    'error_text' => $output['error_text'],
                    'error_title' => $output['error_text']
                ]
            );
        }
    }

    protected function apiSend($options, $api_data = [])
    {
        $isPost = !($options['request_type'] === 'get');
        $api_url = $options['api_url'];
        if (!$isPost) {
            $api_url .= (is_int(strpos($options['api_url'], '?')) ? '&' : '?');
            $api_url .= http_build_query($api_data);
        }

        $curl = curl_init($api_url);
        curl_setopt($curl, CURLOPT_PORT, $options['api_port']);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['X-App-Api-Key: ' . $options['api_key']]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);
        if ($isPost) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($api_data));
        }

        $response = curl_exec($curl);
        if (!$response) {
            $err = new AError('Tims Catalog Sync failed: '.curl_error($curl).'('.curl_errno($curl).')');
            $err->toLog()->toDebug();
            curl_close($curl);
            return false;
        } else {
            $response_data = AJson::decode($response, true);
            curl_close($curl);
            return $response_data;
        }
    }

    public function onControllerPagesCatalogManufacturer_InitData()
    {
        /** @var  ControllerPagesCatalogManufacturer $that */
        $that = $this->baseObject;
        $that->fields[] = 'code';
        $that->fields[] = 'address';
        $that->fields[] = 'contact';
    }

    public function onControllerPagesCatalogManufacturer_UpdateData()
    {
        $that = $this->baseObject;
        if (!in_array($this->baseObject_method, ['insert', 'update'])) {
            return null;
        }

        $form = $that->view->getData('form');

        $form['fields']['general']['contact'] = $that->html->buildElement(
            [
                'type'  => 'input',
                'name'  => 'contact',
                'value' => $that->data['contact'],
                'style' => 'large-field',
            ]
        );
        $that->view->assign('entry_contact', 'Contact Person:');

        $form['fields']['general']['code'] = $that->html->buildElement(
            [
                'type'  => 'input',
                'name'  => 'code',
                'value' => $that->data['code'],
                'style' => 'large-field',
            ]
        );
        $that->view->assign('entry_code', 'Code:');

        $form['fields']['general']['address'] = $that->html->buildElement(
            [
                'type'  => 'input',
                'name'  => 'address',
                'value' => $that->data['address'],
                'style' => 'large-field',
            ]
        );
        $that->view->assign('entry_address', 'Address:');

        $that->view->assign('form', $form);
    }

    public function onControllerPagesCatalogManufacturer_ProcessData()
    {
        $that = $this->baseObject;
        $post = $that->request->post;
        $manufacturer_id = $that->request->get['manufacturer_id'];
        if (!$manufacturer_id) {
            $manufacturer_id = func_get_arg(1)['manufacturer_id'];
        }

        if (!$manufacturer_id) {
            return null;
        }

        $that->db->table('manufacturers')->updateOrInsert(
            ['manufacturer_id' => $manufacturer_id],
            [
                'contact' => $post['contact'],
                'code'    => $post['code'],
                'address' => $post['address'],
            ]
        );
    }

    public function onControllerResponsesListingGridManufacturer_InitData()
    {
        if($this->baseObject_method != 'update_field'){
            return null;
        }
        $that = $this->baseObject;
        $post = $that->request->post;
        $manufacturer_id = $that->request->get['id'];
        $custom_fields = ['contact', 'code', 'address'];
        foreach($post as $field=>$value){
            if(!in_array($field,$custom_fields)){ continue;}
            $that->db->table('manufacturers')->updateOrInsert(
                        ['manufacturer_id' => $manufacturer_id],
                        [
                            $field => $value,

                        ]
                    );
        }
    }

    public function onControllerPagesCatalogProductSummary_UpdateData()
    {
        $that = $this->baseObject;
        $product_info = $that->view->getData('product');
        if (!$product_info['license']) {
            return null;
        }
        /** @var ModelCatalogProduct $mdl */
        $mdl = $that->loadModel('catalog/product');
        $product_id = $that->request->get['product_id'];
        $options = $mdl->getProductOptions($product_id);
        foreach ($options as $option) {
            $values = $option['product_option_value'];
            foreach ($values as $val) {

                if (!$val['sku']) {
                    $product_info['condition'][] =
                        'Empty SKU set for option value (see option "'
                        .$option['language'][$that->language->getContentLanguageID()]['name'].'")';
                }
            }
            $that->view->assign('product', $product_info);
        }
    }

    public function onControllerResponsesListingGridLicensing_UpdateData()
    {
        if ($this->baseObject_method != 'main') {
            return null;
        }
        $that = $this->baseObject;

        if($that->data['response']->rows) {
            foreach ($that->data['response']->rows as &$row) {
                if (!$row['raw_data']['order_id']) {
                    continue;
                }
                if (ABC::env('sites')[$row['raw_data']['site_alias']]['api_url']) {
                    $row['cell']['order_id'] = '<a 
                    target="_new" 
                    href="'.ABC::env('sites')[$row['raw_data']['site_alias']]['api_url']
                        .'&rt=sale/order/details&order_id='.$row['raw_data']['order_id'].'"
                 >#'.$row['raw_data']['order_id'].' ('.$row['raw_data']['site_alias'].')</a>';
                } else {
                    $row['cell']['order_id'] = '#'.$row['raw_data']['order_id'].' ('.$row['raw_data']['site_alias'].')';
                }
            }
        }
    }


    public function onControllerResponsesListingGridProduct_InitData()
    {
        $that = $this->baseObject;
        // UPDATING DATA
        if( isset($that->request->post['product_type']) ){
            $product = Product::find($that->request->get['id']);
            if ($product){
                $value = $that->request->post['product_type'];
                $that->request->post['product_type'] = array_merge( (array)$product->product_type, (array)$value );
            }
        }
        if( isset($that->request->post['uplift_id']) ){
            $product = Product::find($that->request->get['id']);
            if ($product){
                $value = $that->request->post['uplift_id'];
                $that->request->post['uplift_id'] = array_merge( (array)$product->uplift_id, (array)$value );
            }
        }

        if(isset($that->request->post['sites'])){
            if($that->request->post['sites'] == ["''"]){
                $that->request->post['sites'] = [];
            }
        }

        if (isset($that->request->post['display_location'])) {
            if ($that->request->post['display_location'] == ["''"]) {
                $that->request->post['display_location'] = [];
            }
            $that->request->post['display_location'] = json_encode($that->request->post['display_location']);
        }

        if ($that->request->post['oper'] == 'del') {
            $ids = array_unique(explode(',', $that->request->post['id']));
            if (!$ids) {
                return;
            }
            $nonEligibleId = Product::where('sku', '=', 'NOTELIGIBLE')?->first()->product_id;
            if (in_array($nonEligibleId, $ids)) {
                $error = new AError('You cannot to delete NOT_ELIGIBLE_PRODUCT');
                $error->toJSONResponse('NO_PERMISSIONS_402',
                    [
                        'error_text'  => 'You cannot to delete NOT_ELIGIBLE_PRODUCT',
                        'reset_value' => true,
                    ]
                );
                exit;
            }

        }
    }


    //sku related hooks
    public function onControllerResponsesListingGridProduct_ValidateData()
    {
        $that = $this->baseObject;
        $that->loadLanguage('tims_catalog/tims_catalog');
        $args = func_get_args();
        if ($args[0][1] == 'sku') {
            $sku = $args[0][2];
            if ($sku === '') {
                $error = new AError('');
                $error->toJSONResponse('VALIDATION_ERROR_406',
                    [
                        'error_text'  => $that->language->get('tims_error_sku'),
                        'reset_value' => true,
                    ]);
                $that->response->output();
                exit;
            } else {
                $presentsSku = $this->getProductsBySku($sku, (int)$that->request->get['id']);
                if ($presentsSku) {
                    $error_text = $that->language->get('tims_error_sku_non_unique');
                    foreach ($presentsSku as $product) {
                        $error_text .=
                            '<br><a target="_new" href="'
                            .$that->html->getSecureURL('catalog/product/update', '&product_id='.$product['product_id'])
                            .'#productFrm_sku">'.$product['product_name'].'</a>';
                    }
                    $error = new AError('VALIDATION_ERROR_406');
                    $error->toJSONResponse('',
                        [
                            'error_text'  => $error_text,
                            'reset_value' => true,
                        ]);
                    $that->response->output();
                    exit;
                }

            }

        } elseif ($args[0][1] == 'cost') {

            $cost = (float)$args[0][2];
            if (!round($cost, 3)) {
                $error = new AError('VALIDATION_ERROR_406');
                $error->toJSONResponse('',
                    [
                        'error_text'  => $that->language->get('tims_error_cost'),
                        'reset_value' => true,
                    ]);
                $that->response->output();
                exit;
            }
        }elseif ($args[0][1] == 'supplier'){
            $product = Product::find($that->request->get['id']);
            if ($product){
                if($product->license
                    && !in_array($args[0][2], array_keys((array)ABC::env('product')['license_suppliers']))
                ) {
                    $error = new AError('VALIDATION_ERROR_406');
                    $error->toJSONResponse('',
                        [
                            'error_text'  => 'Invalid Supplier Name!',
                            'reset_value' => true,
                        ]);
                    $that->response->output();
                    exit;
                }
            }
        }
    }

    public function onControllerResponsesProductProduct_InitData()
    {

        $that = $this->baseObject;
        if ($this->baseObject_method != 'update_option_values'
            || !$that->request->post['sku']
        ) {
            return null;
        }
        $product = Product::find((int)$that->request->get['product_id']);

        $that->loadLanguage('tims_catalog/tims_catalog');

        //get maximum ID of value to use it for newly adding values
        $max = ProductOptionValue::max('product_option_value_id');

        $i = 1;
        foreach ($that->request->post['sku'] as $opt_value_id=>$sku) {
            //generate sku for license product
            if( in_array('E', $product->product_type)
                &&  $product->supplier && ABC::env('product')['license_suppliers'][$product->supplier]
            ){
                if( is_int( strpos($opt_value_id, 'new' ) ) ){
                    $kk = $max+$i;
                    $i++;
                }else{
                    $kk = $opt_value_id;
                }
                //replace sku if supplier prefix not match
                if(!str_starts_with(
                    $that->request->post['sku'][$opt_value_id],
                    ABC::env('product')['license_suppliers'][$product->supplier]['sku-prefix']
                    )
                ){
                    $sku = ABC::env('product')['license_suppliers'][$product->supplier]['sku-prefix']
                        .$product->product_id
                        .'-'
                        .$kk;
                    $that->request->post['sku'][$opt_value_id] = $sku;
                }

            }

            if ($sku === '') {
                $error = new AError('');
                $error->toJSONResponse('VALIDATION_ERROR_406',
                    [
                        'error_title' => $that->language->get('tims_error_sku'),
                        'reset_value' => true,
                    ]);
                $that->response->output();
                exit;
            } else {
                $presentsSku = $this->getProductsBySku($sku, (int)$that->request->get['product_id'], $opt_value_id);

                if ($presentsSku) {
                    $error_text = $that->language->get('tims_error_sku_non_unique');
                    foreach ($presentsSku as $product) {
                        $error_text .=
                            '<br><a target="_new" href="'
                            .$that->html->getSecureURL('catalog/product/update', '&product_id='.$product['product_id'])
                            .'#productFrm_sku">'.$product['product_name'].'</a>';
                    }
                    $error = new AError('VALIDATION_ERROR_406');
                    $error->toJSONResponse('',
                        [
                            'error_title' => $error_text,
                            'reset_value' => true,
                        ]);
                    $that->response->output();
                    exit;
                }

            }
        }

    }

    protected function getProductsBySku($sku, $product_id = 0, $product_option_value_id = 0)
    {
        $output = [];
        $products = $this->db->table('products')
            ->join('product_descriptions',
                function ($join) {
                    /** @var JoinClause $join */
                    $join->on("products.product_id", "=", "product_descriptions.product_id")
                        ->where("product_descriptions.language_id", "=", "1");
                })
            ->select(['products.*', 'product_descriptions.name as product_name'])
            ->where('sku', '=', $sku)
            ->get();
        if ($products) {
            foreach ($products as $p) {
                //do not allow sku of option value be equal product sku!
                if ($product_id && $product_id == $p->product_id && !$product_option_value_id) {
                    continue;
                }
                $output[$p->product_id] = (array)$p;
            }
        }

        if($output){
            return $output;
        }

        //check options values
        $products = $this->db->table('product_option_values')
            ->join('product_descriptions',
                function ($join) {
                    /** @var JoinClause $join */
                    $join->on("product_option_values.product_id", "=", "product_descriptions.product_id")
                        ->where("product_descriptions.language_id", "=", "1");
                })
            ->select(['product_option_values.*', 'product_descriptions.name as product_name'])
            ->where('sku', '=', $sku)
            ->get();
        if ($products) {
            foreach ($products as $p) {
                //do not allow sku of option value be equal product sku!
                if ($product_id && $product_id == $p->product_id && !$product_option_value_id) {
                    continue;
                }
                if ($product_option_value_id && $p->product_option_value_id == $product_option_value_id) {
                    continue;
                }
                $output[$p->product_id] = (array)$p;
            }
        }
        return $output;
    }

    public function onControllerPagesCatalogProduct_InitData()
    {
        $this->baseObject->load->language('tims_catalog/tims_catalog');

        $that = $this->baseObject;

        if(isset($that->request->post['sites'])){
            if($that->request->post['sites'] == ["''"]){
                $that->request->post['sites'] = [];
            }
        }

        if(isset($that->request->post['display_location'])){
            if($that->request->post['display_location'] == ["''"]){
                $that->request->post['display_location'] = [];
            }
            $that->request->post['display_location'] = json_encode($that->request->post['display_location']);
        }
    }

    public function onControllerPagesCatalogProduct_ValidateData()
    {
        if (func_get_arg(0) != 'validateForm') {
            return null;
        }
        $that = $this->baseObject;
        $post =& $that->request->post;
        if($post['productType'] == 'generic') {
            if (!mb_strlen($post['sku'])) {
                $that->error['sku'] = $that->language->get('tims_error_sku');
            } else {
                $presentsSku = $this->getProductsBySku(
                    $post['sku'],
                    (int)$that->request->get['product_id']
                );

                if ($presentsSku) {
                    $that->error['sku'] = $that->language->get('tims_error_sku_non_unique');
                    foreach ($presentsSku as $product) {
                        $that->error['sku'] .= '<br><a target="_new" href="'
                                                        .$that->html->getSecureURL(
                                                            'catalog/product/update',
                                                            '&product_id='.$product['product_id']
                                               ).'#productFrm_sku">'.$product['product_name'].'</a>';
                    }
                }
            }
        }



        if (!round((float)$post['cost'], 3)) {
            $that->error['cost'] = $that->language->get('tims_error_cost');
        }


        if ( $that->request->get['product_id']
                && in_array('E', $post['product_type'])
        ){
            if( trim($post['supplier']) == '') {
                $that->error['supplier'] = 'Please set Supplier Name!';
            }elseif (
                !str_starts_with(
                    $post['sku'],
                    ABC::env('product')['license_suppliers'][$post['supplier']]['sku-prefix']
                )
            ) {
                    $post['sku'] = ABC::env('product')['license_suppliers'][$post['supplier']]['sku-prefix']
                                   .$that->request->get['product_id'];
            }
        }

        $inputData = func_get_arg(1);
        if($inputData['catalog_only'] == 1){
            foreach(['length', 'width', 'height', 'weight'] as $name) {
                unset($that->error[$name]);
            }
        }

    }

    //disable quick start modal
    public function onControllerPagesIndexHome_UpdateData()
    {

        $this->baseObject->view->assign('shortcut', [
            [
                'href' => $this->html->getSecureURL('catalog/category'),
                'text' => $this->language->get('text_category'),
                'icon' => 'categories_icon.png',
                'disabled' => false,
            ],
            [
                'href' => $this->html->getSecureURL('catalog/product'),
                'text' => $this->language->get('text_product'),
                'icon' => 'products_icon.png',
                'disabled' => false,
            ],
            [
                'href' => $this->html->getSecureURL('catalog/manufacturer'),
                'text' => $this->language->get('text_manufacturer'),
                'icon' => 'brands_icon.png',
                'disabled' => false,
            ],
            [
                'href' => $this->html->getSecureURL('catalog/review'),
                'text' => $this->language->get('text_review'),
                'icon' => 'icon_manage3.png',
                'disabled' => false,
            ],
            [
                'href' => $this->html->getSecureURL('sale/customer'),
                'text' => $this->language->get('text_customer'),
                'icon' => 'customers_icon.png',
                'disabled' => false,
            ],
            [
                'href' => $this->html->getSecureURL('sale/order'),
                'text' => $this->language->get('text_order_short'),
                'icon' => 'orders_icon.png',
                'disabled' => true,
            ],
            [
                'href' => $this->html->getSecureURL('extension/extensions/extensions'),
                'text' => $this->language->get('text_extensions_short'),
                'icon' => 'extensions_icon.png',
                'disabled' => true,
            ],
            [
                'href' => $this->html->getSecureURL('localisation/language'),
                'text' => $this->language->get('text_language'),
                'icon' => 'languages_icon.png',
                'disabled' => true,
            ],
            [
                'href' => $this->html->getSecureURL('design/content'),
                'text' => $this->language->get('text_content'),
                'icon' => 'content_manager_icon.png',
                'disabled' => true,
            ],
            [
                'href' => $this->html->getSecureURL('setting/setting'),
                'text' => $this->language->get('text_setting'),
                'icon' => 'settings_icon.png',
                'disabled' => true,
            ],
            [
                'href' => $this->html->getSecureURL('tool/message_manager'),
                'text' => $this->language->get('text_messages'),
                'icon' => 'icon_messages.png',
                'disabled' => true,
            ],
            [
                'href' => $this->html->getSecureURL('design/layout'),
                'text' => $this->language->get('text_layout'),
                'icon' => 'icon_layouts.png',
                'disabled' => true,
            ],
        ]);
        $this->baseObject->view->assign('quick_start_url', '');
        $this->baseObject->view->assign('tip_content', '');
        $this->baseObject->view->assign('no_payment_installed', '');
    }

    public function onControllerPagesToolImportExport_UpdateData(){
        $that = $this->baseObject;
        if($this->baseObject_method != 'import_wizard'){ return null;}
        if(!$that->view->getData('map') && $that->data['file_format'] == 'external'){
            $that->view->assign('prechecked', ['sku']);
            $that->view->addHookVar(
                'js_hook',
                '$(window).on("load",function(){
                    if($("select[name=table]").val() === ""){
                        $("select[name=table]").val("products").change();
                        }
                    });'
            );
        }
    }

    public function onModelToolImportProcess_UpdateData(){
        /** @see ModelToolImportProcess::importTableCols() */
        if ($this->baseObject_method == 'importTableCols') {
            $this->baseObject->data['output']['products']['columns']['product_descriptions.name']['update'] = false;
            $this->baseObject->data['output']['products']['columns']['products.sku']['required'] = true;
            $this->baseObject->data['output']['products']['columns']['products.uplift_id'] =
                [
                    'title'  => 'Uplift ID',
                    'update' => false,
                    //'required' => true,
                    'alias'  => 'uplift_id',
                ];
            $this->baseObject->data['output']['products']['columns']['products.cost_to_business'] =
                [
                    'title'  => 'Cost To Business',
                    'update' => false,
                    //'required' => true,
                    'alias'  => 'cost_to_business',
                ];
            //this does not do anything as it does not work.
            $this->baseObject->data['output']['products']['columns']['products.sites'] =
                [
                    'title'      => 'Sites Export To',
                    'update'     => false,
                    //'required' => true,
                    'alias'      => 'site aliases',
                    'split'      => 1,
                    'splitter'   => ',',
                    'multivalue' => 1,
                ];
            $this->baseObject->data['output']['products']['columns']['products.display_location'] =
                [
                    'title'      => 'Display Locations',
                    'update'     => false,
                    //'required' => true,
                    'alias'      => 'display_location',
                    'split'      => 0,
                    'multivalue' => 1,
                ];
            $this->baseObject->data['output']['products']['columns']['products.catalog_only'] = [
                'title'  => 'Catalog only mode (1 or 0)',
                'alias'  => 'catalog_only',
                'update' => false,
            ];
            $this->baseObject->data['output']['products']['columns']['products.external_url'] = [
                'title'  => 'External URL',
                'alias'  => 'external_url',
                'update' => false,
            ];
            $this->baseObject->data['output']['products']['columns']['products.nominal_code'] = [
                'title'  => 'Nominal Code',
                'alias'  => 'nominal code',
                'update' => false,
            ];
            $this->baseObject->data['output']['products']['columns']['products.supplier'] = [
                'title'  => 'Supplier',
                'alias'  => 'supplier',
                'update' => false,
            ];
        }
    }

    //change data before record of row
    public function onModelToolImportProcess_InitData(){
        /** @see ModelToolImportProcess::process_products_record() */
        if ($this->baseObject_method == 'process_products_record') {
            /** @var ModelToolImportProcess $that */
            $that = $this->baseObject;
            $productSku = trim($that->data['product_data']['sku']);
            if (!$productSku) {
                $that->errors[] = 'Error!!! Empty SKU for product with name: ' . $that->data['product_data']['name'];
            }

            $supplier = strtoupper($that->data['product_data']['supplier']);
            if ($supplier) {
                $that->data['product_data']['supplier'] = $supplier;
            }

            $requiredOption = str_contains($that->data['product_data']['site aliases'], '|E|') ? 1 : 0;
            $that->data['product_data']['license'] = $requiredOption;
            $that->data['product_data']['catalog_only'] = (int)$that->data['product_data']['catalog_only'];
            if ($that->data['product_data']['date_available']) {
                $that->data['product_data']['date_available'] = Carbon::parse($that->data['product_data']['date_available'])->startOfDay()->toDateTimeString();
            } else {
                $that->data['product_data']['date_available'] = Carbon::now()->startOfDay()->toDateTimeString();
            }

            //set option required and enabled
            $i = 1;
            while (isset($that->data['product_data']['option name ' . $i])) {
                $name = $that->data['product_data']['option name ' . $i];
                $name = trim($name);
                if ($name) {
                    $that->data['settings']['import_col'][] = 'option status ' . $i;
                    $that->data['settings']['products_fields'][] = 'product_options.status';
                    $that->data['product_data']['option status ' . $i] = 1;

                    $that->data['settings']['products_fields'][] = 'product_options.required';
                    if ($requiredOption) {
                        $that->data['settings']['import_col'][] = 'option required ' . $i;
                        $that->data['product_data']['option required ' . $i] = 1;
                    }
                }
                $i++;
            }

            //pass the option values
            $optValNames = [];
            $i = 1;
            while (isset($that->data['product_data']['option value name ' . $i])) {
                $name = $that->data['product_data']['option value name ' . $i];
                $name = trim($name);
                if ($name) {
                    $optValNames[$i] = $name;
                }
                $i++;
            }

            $optValuesSkues = [];
            foreach ($optValNames as $i => $name) {
                $optionValueSku = trim($that->data['product_data']['option value sku ' . $i]);
                if (!$optionValueSku && $supplier) {
                    $defaultSKUPrefix = ABC::env('product')['license_suppliers'][$supplier]['sku-prefix'];
                    $that->data['product_data']['option value sku ' . $i] = $defaultSKUPrefix
                        . '-' . microtime();
                } elseif (!$optionValueSku && !$supplier) {
                    $that->data['product_data']['option value sku ' . $i] = '';
                } elseif ($optionValueSku && $productSku) {
                    //check if other product have this sku
                    /** @var Product $product */
                    $product = Product::with('description')->where('sku', '=', $productSku)->first();
                    $productId = $product?->product_id;
                    if ($productId) {
                        if (ProductOptionValue::where('sku', '=', $optionValueSku)->where('product_id', '<>', $productId)->count()) {
                            $that->errors[] = 'Error! SKU ' . $optionValueSku . ' . Product #' . $productId . '(' . $product->description->name . ') have the same option sku!';
                        }
                    }
                }

                if (in_array($optionValueSku, $optValuesSkues)) {
                    $that->errors[] = 'Error! Product option SKU '
                        . $optionValueSku
                        . ' have duplicate(s) in the import data of product "'
                        . $that->data['product_data']['name'] . '"! Sku must be unique for every option\'s value';
                } else {
                    $optValuesSkues[] = $optionValueSku;
                }

                //DO NOT TRACK STOCKS for license products
                if ($that->data['product_data']['license']) {
                    $that->data['product_data']['option value subtract ' . $i] = 0;
                }
            }
        }
    }

    public function onControllerResponsesProductProduct_ProcessData()
    {
        $that = $this->baseObject;

        if ($this->baseObject_method == 'option_value_row') {
            $form = new AForm('HT');
            $form->setForm([
                'form_name' => 'option_value_form',
            ]);

            $that->data['form']['fields']['subtract'] = $form->getFieldHtml([
                        'type'    => 'selectbox',
                        'name'    => $that->data['form']['fields']['subtract']->name,
                        'value'   => 1,
                        'options' => [
                            0 => $this->language->get('text_no')
                        ],
                        'attr' => 'readonly'
                    ]);
        }
    }

    public function onControllerCommonListingGrid_InitData()
    {
        if ($this->baseObject_method != 'main') {
            return null;
        }
        $that = $this->baseObject;
        if(in_array($that->data['table_id'],['product_grid', 'category_grid', 'manufacturer_grid'])){
            unset($that->data['actions']['edit']['children']['layout']);
        }

        if($that->data['table_id'] == 'product_grid'){
            $that->data['colNames'][2] = $this->language->get('title_sku');
            $that->data['colModel'][2] = [
                'name'  => 'sku',
                'index' => 'sku',
                'align' => 'center',
                'width' => 70,
                'search' => false
            ];
            $that->data['colNames'][3] = 'Supplier';
            $that->data['colModel'][3] = [
                'name'  => 'supplier',
                'index' => 'supplier',
                'align' => 'center',
                'width' => 130,
            ];
            $that->data['colNames'][4] = 'Cost To<br>Business';
            $that->data['colModel'][4] = [
                'name'  => 'cost_to_business',
                'index' => 'cost_to_business',
                'align' => 'center',
                'width' => 60,
                'search' => false
            ];

            $that->data['colNames'][6] = $that->data['colNames'][5];
            $that->data['colModel'][6] = $that->data['colModel'][5];

            $that->data['colNames'][5] = 'Cost in £<br>to points';
            $that->data['colModel'][5] = [
                'name'  => 'cost',
                'index' => 'cost',
                'align' => 'center',
                'width' => 60,
                'search' => false
            ];
            $that->data['colModel'][6]['width'] = 75;
        }
    }

    public function onControllerPagesCatalogProductTabs_UpdateData()
    {
        $that = $this->baseObject;
        unset($that->data['groups']['layout']);

    }

    public function onControllerPagesCatalogCategoryTabs_UpdateData()
    {
        $that = $this->baseObject;
        unset($that->data['groups']['layout']);

    }

    public function onControllerPagesCatalogManufacturerTabs_UpdateData()
    {
        $that = $this->baseObject;
        unset($that->data['groups']['layout']);
    }

    public function onControllerPagesToolImportUpload_ProcessData()
    {
        if(func_get_arg(0) != 'file_uploaded'){
            return;
        }
        $result = func_get_arg(1);
        if(!in_array($result['file_type'], ['csv'])){
            return;
        }

        $file = fopen($result['file'],'r');
        $new_file = fopen($result['file']."_new",'a');
        if(!is_resource($file)){
            return;
        }
        ini_set('auto_detect_line_endings', true);

        while($row = fgets($file)){
            if(!mb_check_encoding($row, ABC::env('APP_CHARSET'))){
                $row = utf8_encode($row);
            }
            fwrite($new_file,$row);
        }

        if(fclose($new_file) && fclose($file)){
            @unlink($result['file']);
            @rename($result['file']."_new",$result['file']);
        }
    }

    public function onControllerResponsesCommonResourceLibrary_UpdateData()
    {
        $that = $this->baseObject;
        $get = $that->request->get;
        if ($get['object_name'] === 'categories'){
            try {
                $category = Category::find($get['object_id']);
                $category?->touch();
            } catch (Exception $e) {
                $messages = $this->registry->get('messages');
                $messages?->saveWarning(__CLASS__, 'Job After Category saved not created! ' . $e->getMessage());
                Registry::log()->error(__CLASS__ . ': ' . $e->getTraceAsString());
            }
        }

        if ( $get['object_name'] === 'manufacturers' ) {
            try {
                $manufacturer = Manufacturer::find($get['object_id']);
                $manufacturer?->touch();
            } catch (Exception $e) {
                $messages = $this->registry->get('messages');
                $messages?->saveWarning(__CLASS__, 'Job After Manufacturer saved not created! ' . $e->getMessage());
                Registry::log()->error(__CLASS__ . ': ' . $e->getTraceAsString());
            }
        }

        //temporary solution for products.
        // TODO:  need to replace this with product model change listener like as category
        if(is_array($get['object_name'])){
            $get['object_name'] = $get['object_name'][0];
            $get['object_id'] = $get['object_id'][0];
        }


        if($get['object_name'] === 'products'
            && ($that->request->is_POST()
                || in_array($this->baseObject_method, ['map','unmap','replace','delete'] )
                || isset($get['map'])
                || isset($get['unmap'])
            )
        ){
            $this->exportProductToSites($get['object_id']);
        }
    }

    protected function exportProductToSites($productId = 0) {
        if (!$productId) {
            return true;
        }
        try {
            $dd = new ADispatcher(
                'task/extension/tims_catalog/export',
                [
                    'task_id' => 0,
                    'step_id' => 0,
                    'settings' => [
                        'products'      => [
                            $productId
                        ],
                        'excludeFields' => [

                        ],
                    ],
                ]
            );
            $json = $dd->dispatchGetOutput('task/extension/tims_catalog/export');
            $output = AJson::decode($json, true);
            //interrupt if failed
            if ($output['result'] === false) {
                $error = new AError('');
                $error->toJSONResponse(
                    'APPLICATION_ERROR_406',
                    [
                        'error_text'  => $output['error_text'],
                        'error_title' => $output['error_text']
                    ]
                );
                return;
            }
        return $output;
        } catch (Exception $e) {
            Registry::log()->error($e->getMessage());
        }
    }
}