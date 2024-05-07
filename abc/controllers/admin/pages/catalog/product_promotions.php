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
use abc\models\catalog\Product;
use abc\models\catalog\ProductDiscount;
use abc\models\catalog\ProductSpecial;
use abc\models\customer\CustomerGroup;
use abc\modules\traits\EditProductTrait;
use Error;
use Exception;
use H;

/**
 * Class ControllerPagesCatalogProductPromotions
 *
 * @package abc\controllers\admin
 */
class ControllerPagesCatalogProductPromotions extends AController
{
    use EditProductTrait;
    public $error = [];

    public function main()
    {

        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $this->loadLanguage('catalog/product');

        $productId = (int)$this->request->get['product_id'];

        if ($this->request->is_POST() && $this->validateForm($this->request->post)) {
            $post = $this->request->post;
            $post['product_id'] = $productId;
            $post['price'] = str_replace(" ", "", $post['price']);
            $post['date_start'] = $post['date_start']
                ? H::dateDisplay2ISO($post['date_start'], $this->language->get('date_format_short'))
                : null;

            $post['date_end'] = $post['date_end']
                ? H::dateDisplay2ISO($post['date_end'], $this->language->get('date_format_short'))
                : null;

            $saved = null;
            $this->db->beginTransaction();
            try {
                if ($post['promotion_type'] == 'discount') {
                    $saved = ProductDiscount::updateOrCreate(
                        ['product_discount_id' => (int)$this->request->get['product_discount_id']],
                        $post
                    );
                    $this->cache->flush('product');
                } elseif ($post['promotion_type'] == 'special') {
                    $saved = ProductSpecial::updateOrCreate(
                        ['product_special_id' => (int)$this->request->get['product_special_id']],
                        $post
                    );
                    $this->cache->flush('product');
                }
                $this->db->commit();
                $this->session->data['success'] = $this->language->get('text_success');
                $this->extensions->hk_ProcessData($this, __FUNCTION__, ['model' => $saved]);
                abc_redirect($this->html->getSecureURL(
                    'catalog/product_promotions',
                    '&product_id=' . $productId)
                );
            } catch (Exception|Error $e) {
                $this->log->critical($e->getMessage());
                $this->error[] = sprintf($this->language->get('error_system'), $this->html->getSecureURL('tool/error_log'));
            }
        }

        $this->data['product_info'] = $productInfo = Product::getProductInfo($productId);
        if (!$productInfo) {
            $this->session->data['warning'] = $this->language->get('error_product_not_found');
            abc_redirect($this->html->getSecureURL('catalog/product'));
        }

        $this->view->assign('error_warning', $this->error['warning'] = implode('<br>', $this->error));
        $this->view->assign('success', $this->session->data['success']);
        if (isset($this->session->data['success'])) {
            unset($this->session->data['success']);
        }

        $this->setBreadCrumbs(
            $productInfo,
            $this->html->getSecureURL('catalog/product_promotions', '&product_id=' . $productId),
            $this->language->get('tab_promotions')
        );

        $this->document->setTitle($productInfo['name'] . ' ' . $this->language->get('tab_promotions'));

        $this->data['customer_groups'] = CustomerGroup::all()?->pluck('name', 'customer_group_id')?->toArray();

        $this->data['form_title'] = $this->language->get('text_edit').'&nbsp;'.$this->language->get('text_product');
        $this->data['product_discounts'] = ProductDiscount::where('product_id', '=', $productId)
            ->orderBy('quantity')
            ->orderBy('priority')
            ->orderBy('price')
            ->useCache('product')
            ->get()?->toArray();

        $this->data['delete_discount'] = $this->html->getSecureURL(
            'catalog/product_promotions/delete',
            '&product_id=' . $productId . '&product_discount_id=%ID%'
        );
        $this->data['update_discount'] = $this->html->getSecureURL(
            'catalog/product_discount_form/update',
            '&product_id=' . $productId . '&product_discount_id=%ID%'
        );

        $this->data['product_specials'] = ProductSpecial::where('product_id', '=', $productId)
            ->orderBy('priority')
            ->orderBy('price')
            ->useCache('product')
            ->get()?->toArray();
        $this->data['delete_special'] = $this->html->getSecureURL(
            'catalog/product_promotions/delete',
            '&product_id=' . $productId . '&product_special_id=%ID%'
        );
        $this->data['update_special'] = $this->html->getSecureURL(
            'catalog/product_special_form/update',
            '&product_id=' . $productId . '&product_special_id=%ID%'
        );

        foreach ($this->data['product_discounts'] as $i => $item) {
            if ($item['date_start'] == '0000-00-00') {
                $this->data['product_discounts'][$i]['date_start'] = '';
            } else {
                $this->data['product_discounts'][$i]['date_start'] = H::dateISO2Display(
                    $this->data['product_discounts'][$i]['date_start'],
                    $this->language->get('date_format_short'));
            }
            if ($item['date_end'] == '0000-00-00') {
                $this->data['product_discounts'][$i]['date_end'] = '';
            } else {
                $this->data['product_discounts'][$i]['date_end'] = H::dateISO2Display(
                    $this->data['product_discounts'][$i]['date_end'],
                    $this->language->get('date_format_short'));
            }
        }
        foreach ($this->data['product_specials'] as $i => $item) {
            if ($item['date_start'] == '0000-00-00') {
                $this->data['product_specials'][$i]['date_start'] = '';
            } else {
                $this->data['product_specials'][$i]['date_start'] = H::dateISO2Display(
                    $this->data['product_specials'][$i]['date_start'],
                    $this->language->get('date_format_short'));
            }
            if ($item['date_end'] == '0000-00-00') {
                $this->data['product_specials'][$i]['date_end'] = '';
            } else {
                $this->data['product_specials'][$i]['date_end'] = H::dateISO2Display(
                    $this->data['product_specials'][$i]['date_end'],
                    $this->language->get('date_format_short'));
            }
        }

        $this->data['button_remove'] = $this->html->buildElement(
            [
                'type'  => 'button',
                'text' => $this->language->get('button_remove')
            ]
        );
        $this->data['button_edit'] = $this->html->buildElement(
            [
                'type'  => 'button',
                'text' => $this->language->get('button_edit')
            ]
        );
        $this->data['button_add_discount'] = $this->html->buildElement(
            [
                'type'  => 'button',
                'text'  => $this->language->get('button_add_discount'),
                'href'  => $this->html->getSecureURL(
                    'catalog/product_discount_form/insert',
                    '&product_id=' . $productId
                )
            ]
        );
        $this->data['button_add_special'] = $this->html->buildElement(
            [
                'type'  => 'button',
                'text'  => $this->language->get('button_add_special'),
                'href'  => $this->html->getSecureURL(
                    'catalog/product_special_form/insert',
                    '&product_id=' . $productId
                )
            ]
        );

        $this->addTabs('promotions');
        $this->addSummary();

        $this->view->assign('help_url', $this->gen_help_url('product_promotions'));
        if ($this->config->get('config_embed_status')) {
            $this->data['embed_url'] = $this->html->getSecureURL(
                'common/do_embed/product',
                '&product_id=' . $productId
            );
        }
        $this->view->batchAssign($this->data);
        $this->processTemplate('pages/catalog/product_promotions.tpl');

        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
    }

    public function delete()
    {
        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $this->loadLanguage('catalog/product');
        if ($this->request->get['product_discount_id']) {
            ProductDiscount::find((int)$this->request->get['product_discount_id'])?->delete();
        } elseif ($this->request->get['product_special_id']) {
            ProductSpecial::find((int)$this->request->get['product_special_id'])?->delete();
        }
        $this->session->data['success'] = $this->language->get('text_success');
        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);

        abc_redirect($this->html->getSecureURL(
                        'catalog/product_promotions',
                        '&product_id='.$this->request->get['product_id']
                    )
        );
    }

    protected function validateForm(array $inData)
    {
        if (!$this->user->canModify('catalog/product_promotions')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        if (H::has_value($inData['promotion_type'])) {
            if ($inData['date_start'] != '0000-00-00' && $inData['date_end'] != '0000-00-00'
                && $inData['date_start'] != ''
                && $inData['date_end'] != ''
                && H::dateFromFormat($inData['date_start'], $this->language->get('date_format_short'))
                >
                H::dateFromFormat($inData['date_end'], $this->language->get('date_format_short'))
            ) {
                $this->error['date_end'] = $this->language->get('error_date');
            }
        }
        $this->extensions->hk_ValidateData($this, __FUNCTION__);
        return (!$this->error);
    }
}