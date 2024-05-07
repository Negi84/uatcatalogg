<?php
/*------------------------------------------------------------------------------
  $Id$

  AbanteCart, Ideal OpenSource Ecommerce Solution
  http://www.AbanteCart.com

  Copyright © 2011-2023 Belavier Commerce LLC

  This source file is subject to Open Software License (OSL 3.0)
  License details is bundled with this package in the file LICENSE.txt.
  It is also available at this URL:
  <http://www.opensource.org/licenses/OSL-3.0>

 UPGRADE NOTE:
   Do not edit or add to this file if you wish to upgrade AbanteCart to newer
   versions in the future. If you wish to customize AbanteCart for your
   needs please refer to http://www.AbanteCart.com for more information.
------------------------------------------------------------------------------*/

namespace abc\controllers\admin;

use abc\core\engine\AController;
use abc\core\engine\AForm;
use abc\core\engine\AResource;
use abc\models\catalog\Product;
use abc\models\order\Order;
use abc\models\system\Store;
use H;


class ControllerPagesSaleCoupon extends AController
{
    public $error = [];
    public $data = [
        'fields' => [
            'coupon_description',
            'code',
            'type',
            'discount',
            'total',
            'logged',
            'shipping',
            'coupon_product',
            'date_start',
            'date_end',
            'uses_total',
            'uses_customer',
            'status',
        ]
    ];

    public function main()
    {
        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $this->document->setTitle($this->language->get('heading_title'));

        $this->document->initBreadcrumb(
            [
                'href'      => $this->html->getSecureURL('index/home'),
                'text'      => $this->language->get('text_home'),
                'separator' => false,
            ]
        );
        $this->document->addBreadcrumb(
            [
                'href'      => $this->html->getSecureURL('sale/coupon'),
                'text'      => $this->language->get('heading_title'),
                'separator' => ' :: ',
                'current'   => true,
            ]
        );

        if (isset($this->session->data['error'])) {
            $this->data['error_warning'] = $this->session->data['error'];

            unset($this->session->data['error']);
        } elseif (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $this->data['success'] = '';
        }

        $grid_settings = [
            //id of grid
            'table_id'       => 'coupon_grid',
            // url to load data from
            'url'            => $this->html->getSecureURL('listing_grid/coupon'),
            'editurl'        => $this->html->getSecureURL('listing_grid/coupon/update'),
            'update_field'   => $this->html->getSecureURL('listing_grid/coupon/update_field'),
            'sortname'       => 'name',
            'sortorder'      => 'asc',
            'multiselect'    => 'true',
            'columns_search' => true,
            // actions
            'actions'        => [
                'edit'   => [
                    'text' => $this->language->get('text_edit'),
                    'href' => $this->html->getSecureURL('sale/coupon/update', '&coupon_id=%ID%'),
                ],
                'save'   => [
                    'text' => $this->language->get('button_save'),
                ],
                'delete' => [
                    'text' => $this->language->get('button_delete'),
                ],
            ],
        ];

        $grid_settings['colNames'] = [
            $this->language->get('column_name'),
            $this->language->get('column_code'),
            $this->language->get('column_discount'),
            $this->language->get('column_date_start'),
            $this->language->get('column_date_end'),
            $this->language->get('column_status'),
        ];
        $grid_settings['colModel'] = [
            [
                'name'   => 'name',
                'index'  => 'name',
                'width'  => 160,
                'align'  => 'left',
                'search' => true,
            ],
            [
                'name'   => 'code',
                'index'  => 'code',
                'width'  => 80,
                'align'  => 'left',
                'search' => true,
            ],
            [
                'name'   => 'discount',
                'index'  => 'discount',
                'width'  => 80,
                'align'  => 'center',
                'search' => false,
            ],
            [
                'name'   => 'date_start',
                'index'  => 'date_start',
                'width'  => 80,
                'align'  => 'center',
                'search' => false,
            ],
            [
                'name'   => 'date_end',
                'index'  => 'date_end',
                'width'  => 80,
                'align'  => 'center',
                'search' => false,
            ],
            [
                'name'   => 'status',
                'index'  => 'status',
                'width'  => 120,
                'align'  => 'center',
                'search' => false,
            ],
        ];

        $statuses = [
            '' => $this->language->get('text_select_status'),
            1  => $this->language->get('text_enabled'),
            0  => $this->language->get('text_disabled'),
        ];

        $form = new AForm();
        $form->setForm([
            'form_name' => 'coupon_grid_search',
        ]);

        $grid_search_form = [];
        $grid_search_form['id'] = 'coupon_grid_search';
        $grid_search_form['form_open'] = $form->getFieldHtml(
            [
                'type'   => 'form',
                'name'   => 'coupon_grid_search',
                'action' => '',
            ]
        );
        $grid_search_form['submit'] = $form->getFieldHtml(
            [
                'type'  => 'button',
                'name'  => 'submit',
                'text'  => $this->language->get('button_go'),
                'style' => 'button1',
            ]
        );
        $grid_search_form['reset'] = $form->getFieldHtml(
            [
                'type'  => 'button',
                'name'  => 'reset',
                'text'  => $this->language->get('button_reset'),
                'style' => 'button2',
            ]
        );
        $grid_search_form['fields']['status'] = $form->getFieldHtml(
            [
                'type'    => 'selectbox',
                'name'    => 'status',
                'options' => $statuses,
            ]
        );

        $grid_settings['search_form'] = true;

        $grid = $this->dispatch('common/listing_grid', [$grid_settings]);
        $this->view->assign('listing_grid', $grid->dispatchGetOutput());
        $this->view->assign('search_form', $grid_search_form);

        $this->document->setTitle($this->language->get('heading_title'));
        $this->view->assign('insert', $this->html->getSecureURL('sale/coupon/insert'));
        $this->view->assign('form_language_switch', $this->html->getContentLanguageSwitcher());

        $this->view->assign('help_url', $this->gen_help_url('coupon_listing'));

        $this->processTemplate('pages/sale/coupon_list.tpl');

        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
    }

    public function insert()
    {
        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->library('json');
        if ($this->request->is_POST() && $this->validateForm()) {
            $post = $this->prepareData($this->request->post);
            $coupon_id = $this->model_sale_coupon->addCoupon($post);

            $this->extensions->hk_ProcessData($this, __FUNCTION__, ['coupon_id' => $coupon_id]);

            $this->session->data['success'] = $this->language->get('text_success');
            abc_redirect($this->html->getSecureURL('sale/coupon/update', '&coupon_id=' . $coupon_id));
        }
        $this->_getForm();
        $this->view->assign('form_language_switch');

        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
    }

    public function update()
    {
        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);
        $coupon_id = $this->request->get['coupon_id'];

        $this->document->setTitle($this->language->get('heading_title'));

        $this->view->assign('success', $this->session->data['success']);
        if (isset($this->session->data['success'])) {
            unset($this->session->data['success']);
        }

        $this->load->library('json');
        if ($this->request->is_POST() && $this->validateForm()) {
            $post = $this->prepareData($this->request->post);

            $this->model_sale_coupon->editCoupon($this->request->get['coupon_id'], $post);
            $this->model_sale_coupon->editCouponProducts($this->request->get['coupon_id'], $post);

            $this->extensions->hk_ProcessData($this, __FUNCTION__, ['coupon_id' => $coupon_id]);

            $this->session->data['success'] = $this->language->get('text_success');
            abc_redirect(
                $this->html->getSecureURL(
                    'sale/coupon/update',
                    '&coupon_id=' . $this->request->get['coupon_id']
                )
            );
        }
        $this->_getForm();
        $this->view->assign('form_language_switch', $this->html->getContentLanguageSwitcher());

        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
    }

    private function _getForm()
    {
        $this->data['token'] = $this->session->data['token'];
        $this->data['cancel'] = $this->html->getSecureURL('sale/coupon');
        $this->data['error'] = $this->error;
        $cont_lang_id = $this->language->getContentLanguageID();

        $this->document->initBreadcrumb(
            [
            'href'      => $this->html->getSecureURL('index/home'),
            'text'      => $this->language->get('text_home'),
            'separator' => false,
            ]
        );
        $this->document->addBreadcrumb(
            [
            'href'      => $this->html->getSecureURL('sale/coupon'),
            'text'      => $this->language->get('heading_title'),
            'separator' => ' :: ',
            ]
        );

        if (H::has_value($this->request->get['coupon_id']) && $this->request->is_GET()) {
            $coupon_info = $this->model_sale_coupon->getCouponByID($this->request->get['coupon_id']);
        }

        $this->data['languages'] = $this->language->getAvailableLanguages();
        foreach ($this->data['fields'] as $f) {
            $this->data[$f] = $this->request->post[$f] ?? $coupon_info[$f] ?? '';
        }

        if (!is_array($this->data['coupon_description'])) {
            if (isset($this->request->get['coupon_id'])) {
                $this->data['coupon_description'] = $this->model_sale_coupon->getCouponDescriptions(
                    $this->request->get['coupon_id']
                );
            } else {
                $this->data['coupon_description'] = [];
            }
        }
        if (!is_array($this->data['coupon_product'])) {
            if (isset($coupon_info)) {
                $this->data['coupon_product'] = $this->model_sale_coupon->getCouponProducts(
                    $this->request->get['coupon_id']
                );
            } else {
                $this->data['coupon_product'] = [];
            }
        }

        //check if coupon is active based on dates and update status
        $now = time();
        if (($this->data['date_start'] && H::dateISO2Int($this->data['date_start']) > $now)
            || ($this->data['date_end'] && H::dateISO2Int($this->data['date_end']) < $now)
        ) {
            $this->data['status'] = 0;
        }

        if (isset($this->request->post['date_start'])) {
            $this->data['date_start'] = $this->request->post['date_start'];
        } elseif (isset($coupon_info)) {
            $this->data['date_start'] = H::dateISO2Display(
                $coupon_info['date_start'],
                $this->language->get('date_format_short')
            );
        } else {
            $this->data['date_start'] = H::dateInt2Display(time(), $this->language->get('date_format_short'));
        }

        if (isset($this->request->post['date_end'])) {
            $this->data['date_end'] = $this->request->post['date_end'];
        } elseif (isset($coupon_info)) {
            $this->data['date_end'] = H::dateISO2Display(
                $coupon_info['date_end'],
                $this->language->get('date_format_short')
            );
        } else {
            $this->data['date_end'] = '';
        }

        if (isset($this->data['uses_total']) && $this->data['uses_total'] == -1) {
            $this->data['uses_total'] = '';
        } elseif (isset($this->data['uses_total']) && $this->data['uses_total'] == '') {
            $this->data['uses_total'] = 1;
        }

        if (isset($this->data['uses_customer']) && $this->data['uses_customer'] == -1) {
            $this->data['uses_customer'] = '';
        } elseif (isset($this->data['uses_customer']) && $this->data['uses_customer'] == '') {
            $this->data['uses_customer'] = 1;
        }

        if (!H::has_value($this->data['status'])) {
            $this->data['status'] = 1;
        }

        if (!H::has_value($this->request->get['coupon_id'])) {
            $this->data['action'] = $this->html->getSecureURL('sale/coupon/insert');
            $this->data['heading_title'] = $this->language->get('text_insert') . ' ' . $this->language->get('text_coupon');
            $this->data['update'] = '';
            $form = new AForm('ST');
        } else {
            $this->data['action'] = $this->html->getSecureURL(
                'sale/coupon/update',
                '&coupon_id=' . $this->request->get['coupon_id']
            );

            $this->data['heading_title'] = $this->language->get('text_edit')
                . ' ' . $this->language->get('text_coupon')
                . ' - ' . $this->data['coupon_description'][$cont_lang_id]['name'];
            $this->data['update'] = $this->html->getSecureURL(
                'listing_grid/coupon/update_field',
                '&id=' . $this->request->get['coupon_id']
            );
            $form = new AForm('HS');
        }

        $this->document->addBreadcrumb(
            [
                'href'      => $this->data['action'],
                'text'      => $this->data['heading_title'],
                'separator' => ' :: ',
                'current'   => true,

            ]
        );

        $form->setForm(
            [
                'form_name' => 'couponFrm',
                'update'    => $this->data['update'],
            ]
        );

        $this->data['form']['id'] = 'couponFrm';
        $this->data['form']['form_open'] = $form->getFieldHtml(
            [
                'type'   => 'form',
                'name'   => 'couponFrm',
                'attr'   => 'data-confirm-exit="true"  class="aform form-horizontal"',
                'action' => $this->data['action'],
            ]
        );
        $this->data['form']['submit'] = $form->getFieldHtml(
            [
                'type'  => 'button',
                'name'  => 'submit',
                'text'  => $this->language->get('button_save'),
                'style' => 'button1',
            ]
        );
        $this->data['form']['cancel'] = $form->getFieldHtml(
            [
                'type'  => 'button',
                'name'  => 'cancel',
                'text'  => $this->language->get('button_cancel'),
                'style' => 'button2',
            ]
        );

        $this->data['form']['fields']['status'] = $form->getFieldHtml(
            [
                'type'  => 'checkbox',
                'name'  => 'status',
                'value' => $this->data['status'],
                'style' => 'btn_switch',
            ]
        );

        $this->data['form']['fields']['name'] = $form->getFieldHtml(
            [
                'type'         => 'input',
                'name'         => 'coupon_description[' . $cont_lang_id . '][name]',
                'value'        => $this->data['coupon_description'][$cont_lang_id]['name'],
                'required'     => true,
                'style'        => 'large-field',
                'multilingual' => true,
            ]
        );
        $this->data['form']['fields']['description'] = $form->getFieldHtml(
            [
                'type'         => 'textarea',
                'name'         => 'coupon_description[' . $cont_lang_id . '][description]',
                'value'        => $this->data['coupon_description'][$cont_lang_id]['description'],
                'required'     => true,
                'style'        => 'large-field',
                'multilingual' => true,
            ]
        );
        $this->data['form']['fields']['code'] = $form->getFieldHtml(
            [
                'type'     => 'input',
                'name'     => 'code',
                'value'    => $this->data['code'],
                'required' => true,
            ]
        );
        $this->data['form']['fields']['type'] = $form->getFieldHtml(
            [
                'type'    => 'selectbox',
                'name'    => 'type',
                'value'   => $this->data['type'],
                'options' => [
                    'P' => $this->language->get('text_percent'),
                    'F' => $this->language->get('text_amount'),
                ],
            ]
        );
        $this->data['form']['fields']['discount'] = $form->getFieldHtml(
            [
                'type'  => 'input',
                'name'  => 'discount',
                'value' => H::moneyDisplayFormat($this->data['discount']),
            ]
        );
        $this->data['form']['fields']['total'] = $form->getFieldHtml(
            [
                'type'  => 'input',
                'name'  => 'total',
                'value' => H::moneyDisplayFormat($this->data['total']),
            ]
        );
        $this->data['form']['fields']['logged'] = $form->getFieldHtml(
            [
                'type'    => 'selectbox',
                'name'    => 'logged',
                'value'   => $this->data['logged'],
                'options' => [
                    1 => $this->language->get('text_yes'),
                    0 => $this->language->get('text_no'),
                ],
            ]
        );
        $this->data['form']['fields']['shipping'] = $form->getFieldHtml(
            [
                'type'    => 'selectbox',
                'name'    => 'shipping',
                'value'   => $this->data['shipping'],
                'options' => [
                    1 => $this->language->get('text_yes'),
                    0 => $this->language->get('text_no'),
                ],
            ]
        );

        $this->data['form']['fields']['date_start'] = $form->getFieldHtml(
            [
                'type'       => 'date',
                'name'       => 'date_start',
                'value'      => $this->data['date_start'],
                'default'    => H::dateNowDisplay(),
                'dateformat' => H::format4Datepicker($this->language->get('date_format_short')),
                'highlight'  => 'future',
                'required'   => true,
            ]
        );

        $this->data['form']['fields']['date_end'] = $form->getFieldHtml(
            [
                'type'       => 'date',
                'name'       => 'date_end',
                'value'      => $this->data['date_end'],
                'default'    => '',
                'dateformat' => H::format4Datepicker($this->language->get('date_format_short')),
                'highlight'  => 'past',
                'required'   => true,
            ]
        );

        $this->data['form']['fields']['uses_total'] = $form->getFieldHtml(
            [
                'type'  => 'input',
                'name'  => 'uses_total',
                'value' => $this->data['uses_total'],
            ]
        );
        $this->data['form']['fields']['uses_customer'] = $form->getFieldHtml(
            [
                'type'  => 'input',
                'name'  => 'uses_customer',
                'value' => $this->data['uses_customer'],
            ]
        );

        $store_name = 'For store ' . (Store::find($this->config->get('config_store_id'))->name ?: 'default') . ': ';

        if ($this->request->get['coupon_id']) {
            $total = Order::search(
                [
                    'filter' => [
                        'coupon_id' => $this->request->get['coupon_id'],
                    ],
                    'mode'   => 'total_only',
                ]
            );
            $this->data['form']['fields']['total_coupon_usage'] = $form->getFieldHtml(
                [
                'type'  => 'input',
                'name'  => 'total_coupon_usage',
                'value' => $store_name . (int)$total,
                'attr'  => 'disabled',
                ]
            );
        }

        //load only prior saved products
        $this->data['products'] = [];
        if (count($this->data['coupon_product'])) {
            $ids = array_map('intval', array_values($this->data['coupon_product']));
            $results = Product::search(['filter' => ['include' => $ids]]);
            $productIds = $results->pluck('product_id')->toArray();

            //get thumbnails by one pass
            $resource = new AResource('image');
            $thumbnails = $productIds
                ? $resource->getMainThumbList(
                    'products',
                    $productIds,
                    $this->config->get('config_image_grid_width'),
                    $this->config->get('config_image_grid_height')
                )
                : [];

            foreach ($results as $r) {
                $id = $r->product_id;
                $this->data['products'][$id]['name'] = $r->name
                    . " (" . $r->model . ") - "
                    . $this->currency->format($r->price);

                $this->data['products'][$id]['image'] = $thumbnails[$id]['thumb_html'];
                //TODO: remove it in the future. See select_chosen.tpl
                $this->data['products'][$id]['image'] .= $this->data['products'][$id]['name'];
            }
        }

        $this->data['form']['fields']['product'] = $form->getFieldHtml(
            [
                'type'        => 'multiselectbox',
                'name'        => 'coupon_product[]',
                'value'       => $this->data['coupon_product'],
                'options'     => $this->data['products'],
                'style'       => 'chosen',
                'ajax_url'    => $this->html->getSecureURL('r/product/product/products'),
                'placeholder' => $store_name . $this->language->get('text_select_from_lookup'),
            ]
        );

        $this->view->assign('help_url', $this->gen_help_url('coupon_edit'));
        $this->view->assign('form_store_switch', $this->html->getStoreSwitcher());
        $this->view->batchAssign($this->data);

        $this->processTemplate('pages/sale/coupon_form.tpl');
    }

    protected function validateForm()
    {
        if (!$this->user->canModify('sale/coupon')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (isset($this->request->post['uses_total']) && $this->request->post['uses_total'] == '') {
            $this->request->post['uses_total'] = -1;
        }

        if (isset($this->request->post['uses_customer']) && $this->request->post['uses_customer'] == '') {
            $this->request->post['uses_customer'] = -1;
        }

        foreach ($this->request->post['coupon_description'] as $value) {
            if (mb_strlen($value['name']) < 2 || mb_strlen($value['name']) > 64) {
                $this->error['name'] = $this->language->get('error_name');
            }

            if (mb_strlen($value['description']) < 2) {
                $this->error['description'] = $this->language->get('error_description');
            }
        }

        if (mb_strlen($this->request->post['code']) < 2 || mb_strlen($this->request->post['code']) > 10) {
            $this->error['code'] = $this->language->get('error_code');
        }

        if (!H::has_value($this->request->post['date_start'])) {
            $this->error['date_start'] = $this->language->get('error_date');
        }
        if (!H::has_value($this->request->post['date_end'])) {
            $this->error['date_end'] = $this->language->get('error_date');
        }
        $this->extensions->hk_ValidateData($this);
        return (!$this->error);
    }

    protected function prepareData(array $inData = [])
    {
        if (H::has_value($inData['date_start'])) {
            $inData['date_start'] = H::dateDisplay2ISO(
                $inData['date_start'],
                $this->language->get('date_format_short')
            );
        }
        if (H::has_value($inData['date_end'])) {
            $inData['date_end'] = H::dateDisplay2ISO(
                $inData['date_end'],
                $this->language->get('date_format_short')
            );
            if (H::dateISO2Int($inData['date_end']) < time()) {
                $inData['status'] = 0;
            }
        }

        $inData['discount'] = H::preformatFloat($inData['discount'], $this->language->get('decimal_point'));
        $inData['total'] = H::preformatFloat($inData['total'], $this->language->get('decimal_point'));

        return $inData;
    }
}