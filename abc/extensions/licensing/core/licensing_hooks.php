<?php
namespace abc\core\extension;

use abc\core\ABC;
use abc\core\engine\AController;
use abc\core\engine\Extension;
use abc\core\engine\Registry;
use abc\core\lib\AEncryption;
use abc\core\view\AView;
use abc\extensions\licensing\models\admin\catalog\ModelCatalogLicensing;
use abc\extensions\licensing\models\storefront\extension\ModelExtensionLicensing;
use abc\models\order\Order;
use abc\models\catalog\Product;
use abc\models\order\OrderStatus;

/**
 * Class ExtensionLicensing
 *
 * @package abc\core\extension
 * @property AController $baseObject
 */
class ExtensionLicensing extends Extension
{
    protected $data = [];

    protected function isEnabled()
    {
        return (bool) $this->baseObject->config->get('licensing_status');
    }

    protected function isLicensedProduct($product_id)
    {
        $that = $this->baseObject;
        $product_info = $that->view->getData('product_info');
        if (!isset($product_info['license'])) {
            $product = Product::find($product_id);
            if($product){
                $product_info = $product->toArray();
            }else{
                $product_info = [];
            }
        }
        return $product_info['license'] > 0;
    }


    public function onControllerPagesCatalogProduct_InitData()
    {
        $that = $this->baseObject;
        if (!$this->isEnabled()) {
            return null;
        }
        if (in_array($this->baseObject_method, ['update', 'insert'])) {
            $that->load->language('licensing/licensing');
        }
    }
    public function onControllerPagesCatalogProduct_ProcessData()
    {
        $that = $this->baseObject;
        $product_id = $that->data['product_id'];

        //if ($this->baseObject_method == 'product_insert' && $that->request->post['productType'] == 'license') {
        //for tims!
        if ( in_array($this->baseObject_method, ['product_insert', 'product_update'])
            && in_array('E', $that->request->post['product_type'])
        ) {
            $product = Product::find($product_id);
            if($product){
                $product->license = 1;
                $product->save();
            }
        }
        if ($this->baseObject_method == 'product_insert' && $that->request->post['productType'] == 'catalog_only') {
            $product = Product::find($product_id);
            $product?->update(
                [
                    'catalog_only' => 1,
                    'external_url' => $that->request->post['external_url'] ? : ''
                ]
            );
        }
    }

    public function onControllerPagesCatalogProduct_UpdateData()
    {
        $that = $this->baseObject;
        if (!$this->isEnabled()) {
            return null;
        }
        $product_id = (int)$that->request->get['product_id'];
        $is_licensed = $this->isLicensedProduct($product_id);


        if (in_array($this->baseObject_method, [ 'update', 'insert' ])) {
            // do redirect when switch product to licensed type
            if ($that->request->get['productType']) {
                $do_redirect = false;
                //set product as donation
                $that->load->model('catalog/licensing');
                /**
                 * @var ModelCatalogLicensing $model
                 */
                $model = $that->{'model_catalog_licensing'};

                //if ($that->request->get['productType'] == 'license') {
                if (in_array($that->request->get['productType'], ABC::env('product')['licensed_product_types'])) {
                    $model->setProductAsLicensed($product_id);
                    $do_redirect = true;
                } elseif ($that->request->get['productType'] == 'generic') {
                    $model->setProductAsGeneric($product_id);
                    $do_redirect = true;
                }

                if ($do_redirect) {
                    abc_redirect(
                        $that->html->getSecureURL(
                            'catalog/product/update',
                            '&product_id='.$product_id
                        )
                    );
                }
            }

            //add switcher to licensed product

            $data = $that->view->getData('form');
            $that->view->assign('entry_productType', $that->language->get('licensing_entry_product_type'));

            $href = $that->html->getSecureURL(
                'catalog/product/'.$this->baseObject_method,
                ($product_id ? '&product_id='.$product_id : '')
            );

            $view = new AView(Registry::getInstance(), 0);
            $view->batchAssign(
                [
                    'text_confirm' => $that->language->get('licensing_switch_confirm_text'),
                    'text_yes' => $that->language->get('text_yes'),
                    'text_no'  => $that->language->get('text_no'),
                    'href'     => $href,
                ]
            );
            $exclude_fields = [
                                'shipping_price',
                                'length',
                                'width',
                                'height',
                                'length_class',
                                'weight',
                                'weight_class',
                                'ship_individually',
                                'free_shipping',
                                'location',
                                'stock_status',
                                'subtract',
                                'shipping',
                                'sku',
                            ];

            $view->assign('exclude_fields', $exclude_fields);
            $js = $view->fetch('common/type_popover.tpl');
            $that->view->append('resources_scripts', $js);


            if ($is_licensed || !$product_id) {
                //remove useless fields
                foreach ($data['fields'] as $section => $fields) {
                    foreach ($fields as $fld_name => $fld) {
                        if (in_array($fld_name, $exclude_fields)) {
                            unset($data['fields'][$section][$fld_name]);
                        }
                    }
                }

                $options = $disabled_options = [];
                $suppliers = [];
                foreach(ABC::env('product')['license_suppliers'] as $id => $s){
                    $suppliers[ $id ] = $s['desc']." (".implode(', ',(array)$s['sites']).")";
                }
                $value = $data['fields']['data']['supplier']->value;
                if(!in_array($value, array_keys($suppliers)) && $product_id) {
                    $options = [''=> Registry::language()->get('text_select')];
                    $that->session->data['error'] = 'Please check Supplier name! ';
                    if($value){
                        $options[$value] = $value;
                        $disabled_options[] = $value;
                    }
                    $value = [''=>''];
                    $error_text = 'Please set Supplier Name!';
                    $that->view->assign('error_warning', $error_text);
                }

                $options = $options + $suppliers;

                $data['fields']['data']['supplier'] = $that->html->buildElement(
                    [
                        'type'     => 'selectbox',
                        'name'     => 'supplier',
                        'options'  => $options,
                        'value'    => $value,
                        'required' => true,
                        'disabled_options' => $disabled_options
                    ]
                );
            }

            $that->view->assign('form', $data);
            $that->view->assign('entry_product_type', $that->language->get('licensing_entry_product_type'));
        }
    }

    public function onControllerPagesCatalogProductTabs_UpdateData()
    {
        $that = &$this->baseObject;
        if (!$this->isLicensedProduct($that->request->get['product_id'])) {
            return null;
        }
        $that->loadLanguage('licensing/licensing');

        $href = $that->html->getSecureURL(
            'catalog/licensing',
            '&product_id='.$that->request->get['product_id']
        );
        $tab_license = $that->language->get('licensing_licenses');
        $that->view->addHookVar(
            'extension_tabs',
            '<li '.($that->request->get['rt'] == 'catalog/licensing' ? 'class="active"' : '').'>'
            .'<a href="'.$href.'"><span>'.$tab_license.'</span></a></li>'
        );
    }

    public function onControllerPagesAccountInvoice_UpdateData()
    {
        if (!$this->isEnabled()) {
            return null;
        }
        $that = &$this->baseObject;
        $that->loadLanguage('licensing/licensing');
        /** @var ModelExtensionLicensing $mdl */
        $mdl = $that->loadModel('extension/licensing');
        $order_id = $that->view->getData('order_id');
        if (!$order_id) {
            return null;
        }

        $order_info = Order::getOrderArray($order_id, null, $that->customer->getId());
        //if order is not complete
        if ($order_info['order_status_id'] != $that->order_status->getStatusByTextId('completed')) {
            return null;
        }

        $licensed_products = $mdl->getOrderLicensedProducts($order_id);
        if (!$licensed_products) {
            return null;
        }

        $products = $that->view->getData('products');
        /**
         * @var AEncryption $enc
         */
        $enc = ABC::getObjectByAlias('AEncryption', [$that->config->get('encryption_key')]);
        foreach ($products as &$product) {
            foreach ($licensed_products as $k => $row) {
                if ($product['id'] == $row['product_id']) {
                    $product_token = $enc->encrypt($order_id.'::'.$row['order_product_id'].'::'.$order_info['email']);
                    $href = $that->html->getSecureURL('r/extension/licensing/getPdf', '&opt='.$product_token);
                    $btn = $that->html->buildElement(
                        [
                            'type'  => 'button',
                            'name'  => 'license_button',
                            'text'  => $that->language->get('licensing_get_certificate'),
                            'href'  => $href,
                            'icon'  => 'fa fa-certificate',
                            'style' => 'btn btn-info pull-right '.(!$row['license_key'] ? 'disabled' : ''),
                        ]
                    );
                    //if license key absent - change order status
                    if (!$row['license_key']
                        && $order_info['order_status_id'] != $that->order_status->getStatusByTextId('processing')
                    ) {
                        $new_order_status_id = $that->order_status->getStatusByTextId('processing');
                        $that->checkout->getOrder()->update(
                            $order_id,
                            $new_order_status_id,
                            'Paused. Waiting for license key.'
                        );
                        $order_status = OrderStatus::with('description')->find($new_order_status_id);
                        $that->view->assign('status', $order_status->description->name);
                    }

                    $that->view->addHookVar('hk_additional_buttons', $btn);
                    unset($licensed_products[$k]);
                }
            }
        }
    }

    protected function getOrderLicenseProducts($order_id)
    {
        $that =& $this->baseObject;
        /** @var ModelExtensionLicensing $mdl */
        $mdl = $that->load->model('extension/licensing');
        return $mdl->getOrderLicensedProducts($order_id);
    }

    protected function getProductsLicenses($product_ids)
    {
        $that =& $this->baseObject;
        /** @var ModelExtensionLicensing $mdl */
        $mdl = $that->load->model('extension/licensing');
        return $mdl->getProductsLicenses($product_ids);
    }

}
