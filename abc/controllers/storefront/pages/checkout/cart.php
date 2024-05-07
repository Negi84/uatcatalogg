<?php
/**
 * AbanteCart, Ideal Open Source Ecommerce Solution
 * http://www.abantecart.com
 *
 * Copyright 2011-2023 Belavier Commerce LLC
 *
 * This source file is subject to Open Software License (OSL 3.0)
 * License details is bundled with this package in the file LICENSE.txt.
 * It is also available at this URL:
 * <http://www.opensource.org/licenses/OSL-3.0>
 *
 * UPGRADE NOTE:
 * Do not edit or add to this file if you wish to upgrade AbanteCart to newer
 * versions in the future. If you wish to customize AbanteCart for your
 * needs please refer to http://www.abantecart.com for more information.
 */

namespace abc\controllers\storefront;

use abc\core\engine\AController;
use abc\core\engine\AForm;
use abc\core\lib\APromotion;
use abc\core\engine\AResource;
use abc\core\lib\ADataset;
use abc\core\lib\AFile;
use abc\core\lib\AWeight;
use abc\extensions\ufs_rio\models\businesstypes\BusinessType;
use abc\models\catalog\Product;
use abc\models\catalog\ProductOption;
use abc\models\customer\Address;
use abc\models\locale\Country;
use abc\modules\traits\ProductOptionsTrait;
use H;


/**
 * Class ControllerPagesCheckoutCart
 *
 * @property AWeight $weight
 */
class ControllerPagesCheckoutCart extends AController
{
    use ProductOptionsTrait;
    public $error = [];

    /**
     * NOTE: this method have a few hk_processData calls.
     */
    public function main()
    {
        $error_msg = [];

        $cart_rt = 'checkout/cart';
        $product_rt = 'product/product';
        $checkout_rt = 'checkout/shipping';
        //is this an embed mode
        if ($this->config->get('embed_mode')) {
            $cart_rt = 'r/checkout/cart/embed';
        }

        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        //adding the code for address
        $addressRequired = true;
        $isProductShippable = false;
        $address = Address::where('customer_id', $this->customer->getId())->select('address_id','customer_id','company','firstname','lastname','address_1','address_2',
        'postcode','city','country_id','zone_id')->get();
        if($address && count($address) > 0){
            $address = $address->first()->toArray();
            if(is_array($address) && $address['address_1'] != null){
                $addressRequired = false;
            }else{
                $addressRequired = true;
            }
        }else{
            $address = NULL;
        }

        if($address != NULL){
            $country = Country::where('country_id', $address['country_id'])->get()->first()->toArray();
            $countryCode = $country['iso_code_3'];
        }else{
            $countryCode = NULL;
        }

        $this->data['addressRequired'] = $addressRequired;
        //adding the code for address
          
        //starting of code
        $newForm = new AForm();
        $newForm->setForm(['form_name' => 'AddressFrm']);
            $this->data['AddressFrm']['form_open'] = $newForm->getFieldHtml(
                [
                    'type'   => 'form',
                    'name'   => 'AddressFrm',
                    'action' => ""
                ]
        );
        $this->data['AddressFrm']['fields']['address']['company'] = $newForm->getFieldHtml(
            [
                'type'     => 'input',
                'name'     => 'company',
                'value' => $address != null ? $address['company'] : '',
                'required' => false,
                'placheholder' => 'Business Name*'
            ]
        );
        $this->data['AddressFrm']['fields']['address']['postcode'] = $newForm->getFieldHtml(
            [
                'type'     => 'input',
                'name'     => 'postcode',
                'value' => $address != null ? $address['postcode'] : '',
                // 'required' => true,
                'placheholder' => 'Postal Code'
            ]
        );
        // if ($countryCode == 'IRL') {
        //     $this->data['AddressFrm']['fields']['address']['postcode']->placeholder = 'Eircode';
        // }


        $this->data['AddressFrm']['fields']['address']['address_1'] = $newForm->getFieldHtml(
            [
                'type'     => 'input',
                'name'     => 'address_1',
                'value' => $address != null ? $address['address_1'] : '',
                // 'required' => true,
                'placheholder' => 'Business Addresss Line 1'
            ]
        );
        $this->data['AddressFrm']['fields']['address']['address_2'] = $newForm->getFieldHtml(
            [
                'type'     => 'input',
                'name'     => 'address_2',
                'value' => $address != null ? $address['address_2'] : '',
                'required' => false,
                'placheholder' => 'Business Addresss Line 2'
            ]
        );
        $this->loadModel('localisation/country');
        $countries = $this->model_localisation_country->getCountries();
        $options = [];
        if (count($countries) > 1) {
            $options = ["FALSE" => $this->language->get('text_select')];
        }
        foreach ($countries as $item) {
            $options[$item['country_id']] = $item['name'];
        }
        $this->data['AddressFrm']['fields']['address']['country_id'] = $newForm->getFieldHtml(
            [
                'type'     => 'selectbox',
                'name'     => 'country_id',
                'options'  => $options,
                'value'    => $address != null ? $address['country_id'] : '',
                // 'required' => true,
            ]);
        $this->data['AddressFrm']['fields']['address']['city'] = $newForm->getFieldHtml(
            [
                'type'     => 'input',
                'name'     => 'city',
                'value' => $address != null ? $address['city'] : '',
                // 'required' => true,
                'placheholder' => 'City / Town'
            ]
        );
        
        $allBizTypes = BusinessType::active()
        ->orderBy('name')
        ->useCache('customer')
        ->get()->toArray();
        // //exclude parents, see RIO-125
        $bizTypes = [];
        foreach ($allBizTypes as $bt) {
            $bizTypes[$bt['id']] = $bt;
        }
        foreach ($bizTypes as $bt) {
            if (isset($bizTypes[$bt['parent_id']])) {
                unset($bizTypes[$bt['parent_id']]);
            }
        }

        $bizTypes = array_map('trim', array_column($bizTypes, 'name', 'id'));
        $bizTypes = ['Business Type*'] + $bizTypes;

        $this->data['AddressFrm']['fields']['address']['business_type'] = $newForm->getFieldHtml(
            [
                'type'             => 'selectbox',
                'name'             => 'business_type',
                'options'          => $bizTypes,
                'value'            => '',
                // 'required'         => true,
                //exclude type according to RIO-125
                'disabled_options' => [(string)array_search('Public Sector', $bizTypes)]
            ]
        );

        $this->data['AddressFrm']['fields']['address'] =
        [
            'business_search' => $newForm->getFieldHtml(
                [
                    'type'        => 'input',
                    'name'        => 'business_search',
                    'value'       => '',
                    'placeholder' =>
                        ($countryCode == 'GBR' || $countryCode == 'NIR'
                            ? $this->language->t('ufs_rio_entry_business_search_gb', 'Enter postcode to find your business')
                            : $this->language->t('ufs_rio_entry_business_search_ie', 'Enter trading name to find your business')
                        )
                ]
            ),
        ]
        +
        array_slice(
            $this->data['AddressFrm']['fields']['address'],
            0,
            1,
            true
        )
       
        + array_slice(
            $this->data['AddressFrm']['fields']['address'],
            1,
            count($this->data['AddressFrm']['fields']['address']) - 1,
            true
        );

        if($countryCode == 'GBR'){
            $this->data['register_country'] = 'UK';
        }else if($countryCode == 'IRL'){
            $this->data['register_country'] = 'IE';
        }else if($countryCode == 'NIR'){
            $this->data['register_country'] = 'NIR';
        }

        //process all possible requests first
        if ($this->request->is_GET() && isset($this->request->get['product_id'])) {

            if (isset($this->request->get['option'])) {
                $option = $this->request->get['option'];
            } else {
                $option = [];
            }

            if (isset($this->request->get['quantity'])) {
                $quantity = $this->request->get['quantity'];
            } else {
                $quantity = 1;
            }

            $this->_unset_methods_data_in_session();

            $this->cart->add($this->request->get['product_id'], $quantity, $option);
            $this->extensions->hk_ProcessData($this, 'add_product');
            abc_redirect($this->html->getSecureURL($cart_rt));

        } else {
            if ($this->request->is_GET() && isset($this->request->get['remove'])) {

                //remove product with button click.
                $this->cart->remove($this->request->get['remove']);
                //remove coupon
                unset($this->session->data['coupon']);
                $this->extensions->hk_ProcessData($this, 'remove_product');
                abc_redirect($this->html->getSecureURL($cart_rt));

            } else {
                if ($this->request->is_POST()) {
                    $post = $this->request->post;
                    //if this is coupon, validate and apply
                    if ((isset($post['reset_coupon']) || isset($post['coupon'])) && !$this->csrftoken->isTokenValid()) {
                        $this->error['error_warning'] = $this->language->get('error_unknown');
                    } else {
                        if (isset($post['reset_coupon'])) {
                            //remove coupon
                            unset($this->session->data['coupon']);
                            $this->data['success'] = $this->language->get('text_coupon_removal');
                            unset($this->session->data['success']);
                            $this->reapplyBalance();
                            //process data
                            $this->extensions->hk_ProcessData($this, 'reset_coupon');

                        } else {
                            if (isset($post['coupon']) && $this->_validateCoupon()) {
                                $this->session->data['coupon'] = $post['coupon'];
                                $this->data['success'] = $this->language->get('text_coupon_success');
                                unset($this->session->data['success']);
                                $this->reapplyBalance();
                                //process data
                                $this->extensions->hk_ProcessData($this, 'apply_coupon');
                            }
                        }
                    }

                    if ($this->error['error_warning']) {
                        $error_msg[] = $this->error['error_warning'];
                    }

                    if (isset($post['quantity'])) {
                        //we update cart
                        if (!is_array($post['quantity'])) {
                            $product_id = (int)$post['product_id'];
                            $options = $post['option'] ?? [];

                            //for FILE-attributes
                            if (H::has_value($this->request->files['option']['name'])) {

                                $fm = new AFile();
                                foreach ($this->request->files['option']['name'] as $id => $name) {
                                    $attribute_data = ProductOption::where('product_id', $product_id)
                                        ->active()
                                        ->where('product_option_id', $id)
                                        ->get();
                                    $file_path_info = $fm->getUploadFilePath(
                                        $attribute_data['settings']['directory'],
                                        $name
                                    );

                                    $options[$id] = $file_path_info['name'];

                                    if (!H::has_value($name)) {
                                        continue;
                                    }

                                    if ($attribute_data['required'] && !$this->request->files['option']['size'][$id]) {
                                        $this->session->data['error'] = $this->language->get('error_required_options');
                                        abc_redirect($_SERVER['HTTP_REFERER']);
                                    }

                                    $file_data = [
                                        'option_id' => $id,
                                        'name'      => $file_path_info['name'],
                                        'path'      => $file_path_info['path'],
                                        'type'      => $this->request->files['option']['type'][$id],
                                        'tmp_name'  => $this->request->files['option']['tmp_name'][$id],
                                        'error'     => $this->request->files['option']['error'][$id],
                                        'size'      => $this->request->files['option']['size'][$id],
                                    ];

                                    $file_errors = $fm->validateFileOption($attribute_data['settings'], $file_data);

                                    if (H::has_value($file_errors)) {
                                        $this->session->data['error'] = implode('<br/>', $file_errors);
                                        abc_redirect($_SERVER['HTTP_REFERER']);
                                    } else {
                                        $result = move_uploaded_file($file_data['tmp_name'], $file_path_info['path']);

                                        if (!$result || $this->request->files['package_file']['error']) {
                                            $this->session->data['error'] .= '<br>Error: '
                                                .H::getTextUploadError($this->request->files['option']['error'][$id]);
                                            abc_redirect($_SERVER['HTTP_REFERER']);
                                        }
                                    }

                                    $dataset = new ADataset('file_uploads', 'admin');
                                    $dataset->addRows(
                                        [
                                            'date_added' => date("Y-m-d H:i:s", time()),
                                            'name'       => $file_path_info['name'],
                                            'type'       => $file_data['type'],
                                            'section'    => 'product_option',
                                            'section_id' => $attribute_data['attribute_id'],
                                            'path'       => $file_path_info['path'],
                                        ]
                                    );

                                }
                            }

                            $textErrors = $this->validateProductOptions($product_id, (array)$options);
                            if ($textErrors){
                                $this->session->data['error'] = $textErrors;
                                //send options values back via _GET
                                abc_redirect(
                                    $this->html->getSecureURL(
                                        $product_rt,
                                        '&product_id='.$post['product_id']
                                        .'&'.http_build_query(['option' => $post['option']])));
                            }

                            $this->cart->add((int)$post['product_id'], (int)$post['quantity'], (array)$options);
                        } else {
                            foreach ($post['quantity'] as $key => $value) {
                                $this->cart->update($key, $value);
                            }
                        }
                        $this->_unset_methods_data_in_session();
                    }

                    if (isset($post['remove'])) {
                        foreach (array_keys($post['remove']) as $key) {
                            $this->cart->remove($key);
                        }
                    }

                    $this->extensions->hk_ProcessData($this);

                    //next page is requested after cart update
                    if (isset($post['next_step'])) {
                        abc_redirect($this->html->getSecureURL($post['next_step']));
                    }

                    if (isset($post['redirect'])) {
                        $this->session->data['redirect'] = $post['redirect'];
                    }

                    if (isset($post['quantity']) || isset($post['remove'])) {
                        $this->_unset_methods_data_in_session();
                        abc_redirect($this->html->getSecureURL($cart_rt));
                    }
                }
            }
        }

        $this->document->setTitle($this->language->get('heading_title'));
        $this->document->resetBreadcrumbs();

        $this->document->addBreadcrumb(
            [
                'href'      => $this->html->getHomeURL(),
                'text'      => $this->language->get('text_home'),
                'separator' => false,
            ]);

        $this->document->addBreadcrumb(
            [
                'href'      => $this->html->getSecureURL('checkout/cart'),
                'text'      => $this->language->get('text_basket'),
                'separator' => $this->language->get('text_separator'),
            ]);

        if ($this->cart->hasProducts()) {

            if (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout')) {
                $error_msg[] = $this->language->get('error_stock');
            }

            $this->loadModel('tool/seo_url', 'storefront');

            $form = new AForm();
            $form->setForm(['form_name' => 'cart']);
            $this->data['form']['form_open'] = $form->getFieldHtml(
                [
                    'type'   => 'form',
                    'name'   => 'cart',
                    'action' => $this->html->getSecureURL($cart_rt),
                ]
            );

            $cart_products = $this->cart->getProducts();

            $product_ids = [];
            foreach ($cart_products as $result) {
                $product_ids[] = (int)$result['product_id'];
            }
            //get the product
            if(isset($product_ids) && isset($product_ids[0])){
                $cartProduct = Product::where('product_id', $product_ids[0])->get()->first();
                if($cartProduct){
                    $cartProduct = $cartProduct->toArray();
                  
                    $isProductShippable = $cartProduct['product_type'] == 'I' ? true: false;
                }
            }
            $this->data['isProductShippable'] = $isProductShippable;
            //get the product

            $resource = new AResource('image');
            $thumbnails = $resource->getMainThumbList(
                'products',
                $product_ids,
                $this->config->get('config_image_cart_width'),
                $this->config->get('config_image_cart_height')
            );

            $products = [];
            foreach ($cart_products as $result) {
                $option_data = [];
                $thumbnail = $thumbnails[$result['product_id']];

                foreach ($result['option'] as $option) {
                    $title = '';
                    if ($option['element_type'] == 'H') {
                        continue;
                    } //hide hidden options

                    $value = $option['value'];
                    // hide binary value for checkbox
                    if ($option['element_type'] == 'C' && in_array($value, [0, 1])) {
                        $value = '';
                    }

                    // strip long textarea value
                    if ($option['element_type'] == 'T') {
                        $title = strip_tags($value);
                        $title = str_replace('\r\n', "\n", $title);

                        $value = str_replace('\r\n', "\n", $value);
                        if (mb_strlen($value) > 64) {
                            $value = mb_substr($value, 0, 64).'...';
                        }
                    }

                    $option_data[] = [
                        'name'  => $option['name'],
                        'value' => $value,
                        'title' => $title,
                    ];

                    // product image by option value
                    $mSizes = [
                        'main'  =>
                            [
                                'width'  => $this->config->get('config_image_cart_width'),
                                'height' => $this->config->get('config_image_cart_height'),
                            ],
                        'thumb' => [
                            'width'  => $this->config->get('config_image_cart_width'),
                            'height' => $this->config->get('config_image_cart_height'),
                        ],
                    ];

                    $main_image =
                        $resource->getResourceAllObjects('product_option_value', $option['product_option_value_id'],
                            $mSizes, 1, false);

                    if (!empty($main_image)) {
                        $thumbnail['origin'] = $main_image['origin'];
                        $thumbnail['title'] = $main_image['title'];
                        $thumbnail['description'] = $main_image['description'];
                        $thumbnail['thumb_html'] = $main_image['thumb_html'];
                        $thumbnail['thumb_url'] = $main_image['thumb_url'];
                    }
                }

                $price_with_tax =
                    $this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax'));

                $products[] = [
                    'remove'     => $form->getFieldHtml(
                        [
                            'type' => 'checkbox',
                            'name' => 'remove['.$result['key'].']',
                        ]),
                    'remove_url' => $this->html->getSecureURL($cart_rt, '&remove='.$result['key']),
                    'key'        => $result['key'],
                    'name'       => $result['name'],
                    'model'      => $result['model'],
                    'thumb'      => $thumbnail,
                    'option'     => $option_data,
                    'quantity'   => $form->getFieldHtml(
                        [
                            'type'  => 'input',
                            'name'  => 'quantity['.$result['key'].']',
                            'value' => $result['quantity'],
                            'attr'  => ' size="3" ',
                            'style' => 'short',
                        ]),
                    'stock'      => $result['stock'],
                    'price'      => $this->currency->format($price_with_tax),
                    'total'      => $this->currency->format_total($price_with_tax, $result['quantity']),
                    'href'       => $this->html->getSEOURL($product_rt,
                        '&product_id='.$result['product_id'].'&key='.$result['key'], true),
                ];
            }

            $this->data['products'] = $products;
            $this->data['form']['update'] = $form->getFieldHtml(
                [
                    'type' => 'submit',
                    'name' => $this->language->get('button_update'),
                ]);

            $this->data['form']['checkout'] = $form->getFieldHtml(
                [
                    'type'  => 'button',
                    'name'  => 'checkout',
                    'text'  => $this->language->get('button_checkout'),
                    'style' => 'button',
                ]);

            if ($this->config->get('config_cart_weight')) {
                $this->data['weight'] =
                    $this->weight->format($this->cart->getWeight(), $this->config->get('config_weight_class'));
            } else {
                $this->data['weight'] = false;
            }

            $display_totals = $this->cart->buildTotalDisplay();
            $this->data['totals'] = $display_totals['total_data'];

            if (isset($this->session->data['redirect'])) {
                $this->data['continue'] = str_replace('&amp;', '&', $this->session->data['redirect']);
                unset($this->session->data['redirect']);
            } else {
                $this->data['continue'] = $this->html->getHomeURL();
            }
            $this->data['form']['continue_shopping'] = $form->getFieldHtml(
                [
                    'type'  => 'button',
                    'name'  => 'continue_shopping',
                    'text'  => $this->language->get('button_shopping'),
                    'style' => 'button',
                    'href'  => $this->data['continue'],
                ]
            );

            $this->data['checkout'] = $this->html->getSecureURL($checkout_rt);
            $this->data['checkout_rt'] = $checkout_rt;

            #Check if order total max/min is set and met
            $cf_total_min = $this->config->get('total_order_minimum');
            $cf_total_max = $this->config->get('total_order_maximum');
            if (!$this->cart->hasMinRequirement()) {
                $this->data['form']['checkout'] = '';
                $error_msg[] =
                    sprintf($this->language->get('error_order_minimum'), $this->currency->format($cf_total_min));
            }
            if (!$this->cart->hasMaxRequirement()) {
                $this->data['form']['checkout'] = '';
                $error_msg[] =
                    sprintf($this->language->get('error_order_maximum'), $this->currency->format($cf_total_max));
            }

            //prepare coupon display
            if ($this->config->get('config_coupon_on_cart_page')) {
                $this->view->assign('coupon_status', $this->config->get('coupon_status'));
                $action = $this->html->getSecureURL($cart_rt);
                $coupon_form = $this->dispatch('blocks/coupon_codes', ['action' => $action]);
                $this->view->assign('coupon_form', $coupon_form->dispatchGetOutput());
            }

            if ($this->config->get('config_shipping_tax_estimate')) {
                $form = new AForm();
                $form->setForm(['form_name' => 'estimate']);
                $this->data['form_estimate']['form_open'] = $form->getFieldHtml(
                    [
                        'type'   => 'form',
                        'name'   => 'estimate',
                        'action' => $this->html->getSecureURL($cart_rt),
                    ]);
                $this->data['estimates_enabled'] = true;
            }
            //try to get shipping address details if we have them
            $country_id = $this->config->get('config_country_id');
            $postcode = $zone_id = '';
            $zone_data = [];
            if ($this->session->data['shipping_address_id']) {
                $shipping_address = [];
                if($address = Address::find($this->session->data['shipping_address_id'])){
                    $shipping_address = $address->toArray();
                }

                $postcode = $shipping_address['postcode'];
                $country_id = $shipping_address['country_id'];
                $zone_id = $shipping_address['zone_id'];
            }
            // use default address of customer for estimate form whe shipping address is unknown
            if (!$zone_id && $this->customer->isLogged()) {
                $payment_address = [];
                if($address = Address::find($this->session->data['payment_address_id'])){
                    $payment_address = $address->toArray();
                }
                $postcode = $payment_address['postcode'];
                $country_id = $payment_address['country_id'];
                $zone_id = $payment_address['zone_id'];
            }

            if ($this->request->post['postcode']) {
                $postcode = $this->request->post['postcode'];
            }
            if ($this->request->post['country'][0]) {
                $country_id = $this->request->post['country'][0];
            }
            if ($this->request->post['country_zones'][0]) {
                $zone_id = $this->request->post['country_zones'][0];
            }
            if ($zone_id) {
                $this->loadModel('localisation/zone', 'storefront');
                $zone_data = $this->model_localisation_zone->getZone($zone_id);
            }

            $this->data['form_estimate']['postcode'] = $form->getFieldHtml(
                [
                    'type'  => 'input',
                    'name'  => 'postcode',
                    'value' => $postcode,
                    'style' => 'short',
                ]);

            $this->data['form_estimate']['country_zones'] = $form->getFieldHtml(
                [
                    'type'        => 'zones',
                    'name'        => 'country',
                    'submit_mode' => 'id',
                    'value'       => $country_id,
                    'zone_name'   => $zone_data['name'],
                    'zone_value'  => $zone_id,
                ]);

            $this->data['form_estimate']['submit'] = $form->getFieldHtml(
                [
                    'type' => 'submit',
                    'name' => $this->language->get('button_text_estimate'),
                ]);

            if ($this->session->data['error']) {
                if (is_array($this->session->data['error'])) {
                    $error_msg = array_merge($error_msg, $this->session->data['error']);
                } else {
                    $error_msg[] = $this->session->data['error'];
                }
                unset($this->session->data['error']);
            }

            $this->view->assign('error_warning', $error_msg);
            $this->view->setTemplate('pages/checkout/cart.tpl');

        } else {
            $this->data['heading_title'] = $this->language->get('heading_title');
            $this->data['text_error'] = $this->language->get('text_error');

            $this->data['button_continue'] = $this->html->buildElement(
                [
                    'name'  => 'continue',
                    'type'  => 'button',
                    'text'  => $this->language->get('button_continue'),
                    'href'  => $this->html->getHomeURL(),
                    'style' => 'button',
                ]);
            if ($this->config->get('embed_mode')) {
                $this->data['back_url'] = $this->html->getNonSecureURL('r/product/category');
            }

            $this->view->setTemplate('pages/error/not_found.tpl');
        }

        $this->view->batchAssign($this->data);
        $this->processTemplate();

        //init controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
    }

    private function _validateCoupon()
    {
        $promotion = new APromotion();
        $coupon = $promotion->getCouponData($this->request->post['coupon']);
        if (!$coupon) {
            $this->error['error_warning'] = $this->language->get('error_coupon');
        }

        $this->extensions->hk_ValidateData($this);

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    public function reapplyBalance()
    {
        $session =& $this->session->data;
        unset($session['used_balance'], $this->request->get['balance'], $session['used_balance_full']);
        $balance = $this->customer->getBalance();
        $order_totals = $this->cart->buildTotalDisplay(true);
        $order_total = $order_totals['total'];
        if ($session['used_balance']) {
            #check if we still have balance.
            if ($session['used_balance'] <= $balance) {
                $session['used_balance_full'] = true;
            } else {
                //if balance become less or 0 reapply partial
                $session['used_balance'] = $balance;
                $session['used_balance_full'] = false;
            }
        } else {
            if ($balance > 0) {
                if ($balance >= $order_total) {
                    $session['used_balance'] = $order_total;
                    $session['used_balance_full'] = true;

                } else { //partial pay
                    $session['used_balance'] = $balance;
                    $session['used_balance_full'] = false;
                }
            }
        }
        //if balance enough to cover order amount
        if ($session['used_balance_full']) {
            $session['payment_method'] = [
                'id'    => 'no_payment_required',
                'title' => $this->language->get('no_payment_required'),
            ];
        }
    }

    private function _unset_methods_data_in_session()
    {
        unset($this->session->data['shipping_methods'],
            $this->session->data['shipping_method'],
            $this->session->data['payment_methods'],
            $this->session->data['payment_method']);
    }
}