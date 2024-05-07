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
use abc\models\catalog\Category;
use abc\models\catalog\Product;
use abc\models\system\Store;
use abc\modules\traits\EditProductTrait;

class ControllerPagesCatalogProductRelations extends AController
{
    use EditProductTrait;
    public $error = [];

    public function main()
    {
        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);
        $productId = (int)$this->request->get['product_id'];

        $this->loadLanguage('catalog/product');

        if ($this->request->is_POST()) {
            $this->request->post['product_category'] = array_filter($this->request->post['product_category'] ?: []);
            $this->request->post['product_store'] = (array)$this->request->post['product_store'] ?: [];
            $this->request->post['product_related'] = array_filter($this->request->post['product_related'] ?: []);
            Product::updateProductLinks($productId, $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            abc_redirect($this->html->getSecureURL('catalog/product_relations', '&product_id=' . $productId));
        }

        $this->data['product_info'] = $productInfo = Product::getProductInfo($productId);
        if (!$productInfo) {
            $this->session->data['warning'] = $this->language->get('error_product_not_found');
            abc_redirect($this->html->getSecureURL('catalog/product'));
        }

        $this->view->assign('error_warning', $this->error['warning']);
        $this->view->assign('success', $this->session->data['success']);
        if (isset($this->session->data['success'])) {
            unset($this->session->data['success']);
        }

        $this->setBreadCrumbs(
            $productInfo,
            $this->html->getSecureURL('catalog/product_relations', '&product_id=' . $productId),
            $this->language->get('tab_relations')
        );

        $this->document->setTitle($productInfo['name'] . ' ' . $this->language->get('tab_relations'));

        $this->data['categories'] = array_column(Category::getCategories(), 'name', 'category_id');

        $this->data['stores'] = [
                0 => $this->language->get('text_default')
            ]
            + Store::all()?->pluck('name', 'store_id')?->toArray();

        $this->addTabs('relations');

        $this->data['action'] = $this->html->getSecureURL('catalog/product_relations', '&product_id=' . $productId);
        $this->data['form_title'] = $this->language->get('text_edit') . '&nbsp;' . $this->language->get('text_product');
        $this->data['update'] = $this->html->getSecureURL(
            'listing_grid/product/update_relations_field',
            '&id=' . $productId
        );
        $form = new AForm('HS');

        $form->setForm(
            [
                'form_name' => 'productFrm',
                'update'    => $this->data['update'],
            ]
        );

        $this->data['form']['id'] = 'productFrm';
        $this->data['form']['form_open'] = $form->getFieldHtml(
            [
                'type'   => 'form',
                'name'   => 'productFrm',
                'action' => $this->data['action'],
                'attr'   => 'data-confirm-exit="true" class="aform form-horizontal"',
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
                'href'  => $this->html->getSecureURL('catalog/product/update', '&product_id=' . $productId),
                'name'  => 'cancel',
                'text'  => $this->language->get('button_cancel'),
                'style' => 'button2',
            ]
        );
        $this->data['cancel'] = $this->html->getSecureURL('catalog/product');

        $results = Category::getCategories();
        $this->data['categories'] = array_column($results, 'name', 'category_id');

        $this->data['form']['fields']['category'] = $form->getFieldHtml(
            [
                'type'        => 'checkboxgroup',
                'name'        => 'product_category[]',
                'value'       => array_column($productInfo['categories'], 'category_id', 'category_id'),
                'options'     => $this->data['categories'],
                'style'       => 'chosen',
                'placeholder' => $this->language->get('text_select_category'),
            ]
        );

        //load only prior saved products
        $this->data['products'] = [];
        if ($productInfo['related']) {
            $product_ids = array_column($productInfo['related'], 'product_id');

            //get thumbnails by one pass
            $resource = new AResource('image');
            $thumbnails = $product_ids
                ? $resource->getMainThumbList(
                    'products',
                    $product_ids,
                    $this->config->get('config_image_grid_width'),
                    $this->config->get('config_image_grid_height')
                )
                : [];

            foreach ($productInfo['related'] as $r) {
                $thumbnail = $thumbnails[$r['product_id']];
                $this->data['products'][$r['product_id']] =
                    [
                        'name'  => $r['description']['name'] . " (" . $r['model'] . ")",
                        'image' => $thumbnail['thumb_html'],
                        'price' => $this->currency->format($r['price'])
                    ];
                //Todo: remove after chosen_select.tpl rewrite
                $this->data['products'][$r['product_id']]['image'] .= '&nbsp'
                    . $this->data['products'][$r['product_id']]['name']
                    . ' - ' . $this->data['products'][$r['product_id']]['price'];
            }
        }

        $this->data['form']['fields']['related'] = $form->getFieldHtml(
            [
                'type'        => 'multiselectbox',
                'name'        => 'product_related[]',
                'value'       => array_column($productInfo['related'], 'product_id', 'product_id'),
                'options'     => $this->data['products'],
                'style'       => 'chosen',
                'ajax_url'    => $this->html->getSecureURL(
                    'r/product/product/products',
                    '&exclude[]=' . $productId
                ),
                'placeholder' => $this->language->get(
                    'text_select_from_lookup'
                ),
            ]
        );

        $this->data['form']['fields']['store'] = $form->getFieldHtml(
            [
                'type'    => 'checkboxgroup',
                'name'    => 'product_store[]',
                'value'   => array_column($productInfo['stores'], 'store_id', 'store_id'),
                'options' => $this->data['stores'],
                'style'   => 'chosen',
            ]
        );
        if ($this->config->get('config_embed_status')) {
            $this->data['embed_url'] = $this->html->getSecureURL(
                'common/do_embed/product',
                '&product_id=' . $productId
            );
        }
        $this->addSummary();
        $this->view->assign('help_url', $this->gen_help_url('product_relations'));
        $this->view->batchAssign($this->data);
        $this->processTemplate('pages/catalog/product_relations.tpl');

        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
    }
}