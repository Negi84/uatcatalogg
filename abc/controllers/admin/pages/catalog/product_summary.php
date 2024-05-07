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

use abc\core\ABC;
use abc\core\engine\AController;
use abc\core\engine\AResource;
use abc\models\catalog\Product;
use abc\models\order\Order;
use Illuminate\Support\Carbon;
use Ramsey\Collection\Collection;

class ControllerPagesCatalogProductSummary extends AController
{
    public function main()
    {
        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $this->loadLanguage('catalog/product');
        $productId = (int)$this->request->get['product_id'];
        if (!$productId) {
            return;
        }
        $this->data['product'] = $this->getProductCondition($productId);

        if (!$this->data['product'] && $productId) {
            abc_redirect($this->html->getSecureURL('catalog/product'));
        }

        $this->data['text_product_condition'] = $this->language->get('text_product_condition');
        $this->data['text_product_available'] = $this->language->get('text_product_available');

        $resource = new AResource('image');
        $thumbnail = $resource->getMainThumb(
            'products',
            $this->request->get['product_id'],
            150,
            150,
            true
        );
        $this->data['product']['image'] = $thumbnail;
        $this->data['product']['preview'] = $this->html->getCatalogURL(
            'product/product',
            '&product_id=' . $productId
        );

        //if auditLog storage not found - disable menu item
        if ($this->registry->get('AuditLogStorage') || ABC::getObjectByAlias('AuditLogStorage')) {
            $this->data['auditLog'] = $this->html->buildElement(
                [
                    'type'  => 'button',
                    'text'  => $this->language->get('text_audit_log'),
                    'href'  => $this->html->getSecureURL(
                        'tool/audit_log',
                        '&modal_mode=1'
                        . '&auditable_type=Product'
                        . '&auditable_id=' . $productId
                    ),
                    //quick view port URL
                    'vhref' => $this->html->getSecureURL(
                        'r/common/viewport/modal',
                        '&viewport_rt=tool/audit_log'
                        . '&modal_mode=1'
                        . '&auditable_type=Product'
                        . '&auditable_id=' . $productId
                    ),
                ]
            );
        }
        /** @see Order::getOrders() */
        $this->data['product']['orders'] = Order::search(
            [
                'filter' => [
                    'product_id' => $productId,
                ],
                'mode'   => 'total_only',
            ]
        );

        $this->data['product']['orders_url'] = $this->html->getSecureURL(
            'sale/order',
            '&product_id=' . $productId);

        $this->view->assign('help_url', $this->gen_help_url('product_summary'));
        $this->view->batchAssign($this->data);
        $this->processTemplate('pages/catalog/product_summary.tpl');

        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
    }

    /**
     * function checks if product will be displayed on storefront and returns array with messages about causes
     *
     * @param $product_id
     *
     * @return array
     * @throws \Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getProductCondition($product_id)
    {
        $product_id = (int)$product_id;
        if (!$product_id) {
            return [];
        }


        /** @var Product|Collection $product */
        $product = Product::with('description', 'options.description', 'options.values', 'options.values.description')
            ->where('product_id', $product_id)
            ->useCache('product')
            ->first();
        if (!$product) {
            return false;
        }

        $product->format_price = $this->currency->format($product->price);

        // id product disabled do not run other checks
        if (!$product->status) {
            $product->condition = [$this->language->get('text_product_disabled')];
            return $product;
        }

        $hasTrackOptions = false;
        foreach ($product->options as $option) {
            if (!$option->status) {
                continue;
            }
            foreach ($option->values as $value) {
                if ($value->subtract) {
                    $hasTrackOptions = true;
                    break 2;
                }
            }
        };

        $output = [];
        // check is product available
        if ($product->date_available->startOfDay()->gt(Carbon::now())) {
            $output[] = $this->language->get('text_product_unavailable');
        }

        //check is stock track for whole product(not options) enabled and product quantity more than 0
        if ($product->subtract && $product->quantity <= 0 && !$hasTrackOptions) {
            $output[] = $this->language->get('text_product_out_of_stock');
        }
        $out_of_stock = false;
        $error_txt = [];
        if ($hasTrackOptions) {
            foreach ($product->options as $option) {
                if (!$option->status) {
                    continue;
                }
                foreach ($option->values as $value) {
                    if ($value->subtract && $value->quantity <= 0) {
                        $error_txt[] = $option->description->name . ' => ' . $value->description->name;
                        $out_of_stock = true;
                    }
                }
            }
        }

        if ($out_of_stock && $hasTrackOptions) {
            $output[] = $this->language->get('text_product_option_out_of_stock');
            $output = array_merge($output, $error_txt);
        }
        $product->condition = $output;
        return $product;
    }

}
