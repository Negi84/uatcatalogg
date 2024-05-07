<?php
/**
 * AbanteCart, Ideal Open Source Ecommerce Solution
 * https://www.abantecart.com
 *
 * Copyright (c) 2011-2023  Belavier Commerce LLC
 *
 * This source file is subject to Open Software License (OSL 3.0)
 * License details is bundled with this package in the file LICENSE.txt.
 * It is also available at this URL:
 * <https://www.opensource.org/licenses/OSL-3.0>
 *
 * UPGRADE NOTE:
 * Do not edit or add to this file if you wish to upgrade AbanteCart to newer
 * versions in the future. If you wish to customize AbanteCart for your
 * needs please refer to https://www.abantecart.com for more information.
 */

namespace abc\controllers\admin;

use abc\core\engine\AController;
use abc\core\engine\AForm;
use abc\models\catalog\ProductDescription;
use abc\models\catalog\ProductDiscount;
use abc\models\customer\CustomerGroup;
use H;


class ControllerResponsesCatalogProductDiscountForm extends AController
{
    public $data = [
        'fields' => [
            'customer_group_id',
            'quantity',
            'priority',
            'price',
            'date_start',
            'date_end'
        ]
    ];
    public $error;

    public function insert()
    {
        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $this->loadLanguage('catalog/product');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->_getForm();
        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
    }

    public function update()
    {

        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $this->loadLanguage('catalog/product');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->_getForm();
        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
    }


    protected function _getForm()
    {

        $productId = (int)$this->request->get['product_id'];
        $discountId = (int)$this->request->get['product_discount_id'];

        $this->view->batchAssign($this->language->getASet('catalog/product'));

        $this->view->assign('error_warning', $this->error['warning']);
        $this->view->assign('success', $this->session->data['success']);
        if (isset($this->session->data['success'])) {
            unset($this->session->data['success']);
        }

        $this->data['error'] = $this->error;
        $this->data['cancel'] = $this->html->getSecureURL('catalog/product_promotions', '&product_id=' . $productId);

        $this->data['active'] = 'promotions';
        $productName = ProductDescription::where('product_id', $productId)
            ->where('language_id', $this->language->getLanguageID())
            ->first()->name;
        $this->data['heading_title'] = $this->language->get('text_edit')
            . ' - '
            . $productName;


        if ($discountId) {
            $discountInfo = ProductDiscount::find($discountId)?->toArray();
            foreach (['date_start', 'date_end'] as $dateName) {
                if ($discountInfo[$dateName] == '0000-00-00') {
                    $discountInfo[$dateName] = '';
                } else {
                    $discountInfo[$dateName] = H::dateISO2Display(
                        $discountInfo[$dateName],
                        $this->language->get('date_format_short'));
                }
            }
            $this->data = array_merge($this->data, (array)$discountInfo);
        }

        $this->data['customer_groups'] = CustomerGroup::all()?->pluck('name', 'customer_group_id')?->toArray();

        if (!$discountId) {
            $this->data['action'] = $this->html->getSecureURL(
                'catalog/product_promotions',
                '&product_id=' . $this->request->get['product_id']
            );
            $this->data['form_title'] = $this->language->get('text_insert') . '&nbsp;' . $this->language->get('entry_discount');
            $this->data['update'] = '';
            $form = new AForm('ST');
        } else {
            $this->data['action'] = $this->html->getSecureURL(
                'catalog/product_promotions',
                '&product_id=' . $productId . '&product_discount_id=' . $discountId
            );
            $this->data['form_title'] = $this->language->get('text_edit')
                . '&nbsp;' . $this->language->get('entry_discount');
            $this->data['update'] = $this->html->getSecureURL(
                'listing_grid/product/update_discount_field',
                '&id=' . $discountId
            );
            $form = new AForm('HS');
        }

        $form->setForm(
            [
                'form_name' => 'productFrm',
                'update' => $this->data['update'],
            ]
        );

        $this->data['form']['id'] = 'productFrm';
        $this->data['form']['form_open'] = $form->getFieldHtml(
                [
                    'type' => 'form',
                    'name' => 'productFrm',
                    'action' => $this->data['action'],
                    'attr' => 'data-confirm-exit="true"  class="aform form-horizontal"',
                ]
            ) .
            $form->getFieldHtml(
                [
                    'type'  => 'hidden',
                    'name'  => 'promotion_type',
                    'value' => 'discount'
                ]
            );

        $this->data['form']['submit'] = $form->getFieldHtml(
            [
                'type' => 'button',
                'name' => 'submit',
                'text' => $this->language->get('button_save'),
                'style' => 'button1',
            ]
        );
        $this->data['form']['cancel'] = $form->getFieldHtml(
            [
                'type' => 'button',
                'name' => 'cancel',
                'text' => $this->language->get('button_cancel'),
                'style' => 'button2',
            ]
        );

        $this->data['form']['fields']['customer_group'] = $form->getFieldHtml(
            [
                'type'  => 'selectbox',
                'name'  => 'customer_group_id',
                'value' => $this->data['customer_group_id'],
                'options' => $this->data['customer_groups'],
            ]
        );

        $this->data['form']['fields']['quantity'] = $form->getFieldHtml(
            [
                'type' => 'number',
                'name' => 'quantity',
                'value' => $this->data['quantity'],
                'style' => 'small-field',
            ]
        );
        $this->data['form']['fields']['priority'] = $form->getFieldHtml(
            [
                'type' => 'number',
                'name'  => 'priority',
                'value' => $this->data['priority'],
                'style' => 'small-field',
            ]
        );
        $this->data['form']['fields']['price'] = $form->getFieldHtml(
            [
                'type'  => 'input',
                'name'  => 'price',
                'value' => H::moneyDisplayFormat($this->data['price']),
                'style' => 'tiny-field'
            ]
        );

        $this->data['js_date_format'] = H::format4Datepicker($this->language->get('date_format_short'));
        $this->data['form']['fields']['date_start'] = $form->getFieldHtml(
            [
                'type'      => 'date',
                'name'      => 'date_start',
                'value'     => H::dateISO2Display($this->data['date_start'], $this->language->get('date_format_short')),
                'default'   => '',
                'dateformat' => H::format4Datepicker($this->language->get('date_format_short')),
                'highlight' => 'future',
                'style'     => 'small-field',
            ]
        );

        $this->data['form']['fields']['date_end'] = $form->getFieldHtml(
            [
                'type'      => 'date',
                'name'      => 'date_end',
                'value'     => H::dateISO2Display($this->data['date_end'], $this->language->get('date_format_short')),
                'default'   => '',
                'dateformat' => H::format4Datepicker($this->language->get('date_format_short')),
                'highlight' => 'future',
                'style'     => 'small-field',
            ]
        );

        $this->view->assign('help_url', $this->gen_help_url('product_discount_edit'));
        $this->view->batchAssign($this->data);
        $this->data['response'] = $this->view->fetch('responses/catalog/product_promotion_form.tpl');
        $this->response->setOutput($this->data['response']);
    }
}