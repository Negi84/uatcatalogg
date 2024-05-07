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

use abc\core\ABC;
use abc\core\engine\AController;
use abc\core\engine\AResource;
use abc\models\catalog\Product;
use abc\modules\traits\EditProductTrait;
use H;

if (!class_exists('abc\core\ABC') || !ABC::env('IS_ADMIN')) {
    header('Location: static_pages/?forbidden=' . basename(__FILE__));
}

class ControllerPagesCatalogProductFiles extends AController
{
    use EditProductTrait;
    public $error = [];

    public function main()
    {
        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $this->loadLanguage('catalog/files');
        $this->loadModel('catalog/download');
        $productId = $this->request->get['product_id'];
        $downloadId = $this->request->get['download_id'];

        if (!$productId) {
            abc_redirect($this->html->getSecureURL('catalog/product'));
        }

        $this->data['product_info'] = $productInfo = Product::getProductInfo($productId);
        if (!$productInfo) {
            $this->session->data['warning'] = $this->language->get('error_product_not_found');
            abc_redirect($this->html->getSecureURL('catalog/product'));
        }

        // remove
        if (H::has_value($this->request->get['act']) && $this->request->get['act'] == 'delete') {
            $download_info = $this->model_catalog_download->getDownload($downloadId);
            $map_list = $this->model_catalog_download->getDownloadMapList($downloadId);

            if ((sizeof($map_list) == 1 && key($map_list) == $productId) || $download_info['shared'] != 1) {
                $this->model_catalog_download->deleteDownload($downloadId);
            } else {
                $this->model_catalog_download->unmapDownload($downloadId, $productId);
            }
            $this->session->data['success'] = $this->language->get('text_success_remove');
            abc_redirect($this->html->getSecureURL('catalog/product_files', '&product_id=' . $productId));
        }


        //Downloads disabled. Warn user
        if (!$this->config->get('config_download')) {
            $this->error['warning'] = $this->html->convertLinks($this->language->get('error_downloads_disabled'));
        }

        if ($this->request->is_POST() && $this->validateForm()) {
            foreach ($this->request->post['selected'] as $id) {
                $this->model_catalog_download->mapDownload($id, $productId);
            }

            $this->session->data['success'] = $this->language->get('text_map_success');
            abc_redirect($this->html->getSecureURL('catalog/product_files', '&product_id=' . $productId));
        }

        $this->view->assign('error_warning', $this->error['warning']);
        $this->view->assign('success', $this->session->data['success']);

        if (isset($this->session->data['success'])) {
            unset($this->session->data['success']);
        }

        $this->setBreadCrumbs(
            $productInfo,
            $this->html->getSecureURL('catalog/product_files', '&product_id=' . $productId),
            $this->language->get('tab_files')
        );
        $this->document->setTitle($productInfo['name'] . ' ' . $this->language->get('tab_files'));

        $this->addTabs('files');
        $this->addSummary();

        $this->loadModel('catalog/download');
        $this->data['downloads'] = [];

        $this->data['product_files'] = $this->model_catalog_download->getProductDownloadsDetails($productId);

        $rl = new AResource('download');
        $rl_dir = $rl->getTypeDir();
        foreach ($this->data['product_files'] as &$file) {
            $resource_id = $rl->getIdFromHexPath(str_replace($rl_dir, '', $file['filename']));
            $resource_info = $rl->getResource($resource_id);
            $thumbnail = $rl->getResourceThumb($resource_id, $this->config->get('config_image_grid_width'),
                $this->config->get('config_image_grid_height'));
            if ($resource_info['resource_path']) {
                $file['icon'] = $this->html->buildResourceImage(
                    [
                        'url'    => $thumbnail,
                        'width'  => $this->config->get('config_image_grid_width'),
                        'height' => $this->config->get('config_image_grid_height'),
                        'attr' => 'alt="' . $resource_info['title'] . '"',
                    ]);
            } else {
                $file['icon'] = $resource_info['resource_code'];
            }

            $file['status'] = $file['status']
                ? $this->language->get('text_enabled')
                : $this->language->get('text_disabled');

            $file['button_edit'] = $this->html->buildElement(
                [
                    'type' => 'button',
                    'text' => $this->language->get('button_edit'),
                    'href' => $this->html->getSecureURL(
                        'r/product/product/buildDownloadForm',
                        '&product_id=' . $productId . '&download_id=' . $file['download_id'])
                ]
            );

            $map_list = $this->model_catalog_download->getDownloadMapList($file['download_id']);
            if ((sizeof($map_list) == 1 && key($map_list) == $productId) || $file['shared'] != 1) {
                $text = $this->language->get('button_delete');
                $icon = 'fa-trash-o';
            } else {
                $text = $this->language->get('button_unmap');
                $icon = 'fa-chain-broken';
            }

            $file['button_delete'] = $this->html->buildElement(
                [
                    'type' => 'button',
                    'text' => $text,
                    'href' => $this->html->getSecureURL('catalog/product_files',
                        '&act=delete&product_id=' . $productId . '&download_id=' . $file['download_id']),
                    'icon' => $icon,
                ]
            );

            $orders_count = $this->model_catalog_download->getTotalOrdersWithProduct($productId);
            if ($orders_count) {
                $file['push_to_customers'] = $this->html->buildElement(
                    [
                        'type'  => 'button',
                        'name'  => 'push_to_customers',
                        'text'  => sprintf($this->language->get('text_push_to_orders'), $orders_count),
                        'title' => $this->language->get('text_push'),
                        'icon'  => 'fa-share-alt-square',
                        'href' => $this->html->getSecureURL(
                            'catalog/product_files/pushToCustomers',
                            '&product_id=' . $productId . '&download_id=' . $file['download_id']
                        ),
                        'attr' => 'data-orders-count="' . $orders_count . '"',
                    ]
                );
            }
            if ($file['map_list']) {
                foreach ($file['map_list'] as $k => &$item) {
                    $new = [
                        'product_id' => $k,
                        'name'       => $item,
                        'url'        => $this->html->getSecureURL(
                            'catalog/product_files',
                            '&product_id=' . $k
                        )
                    ];
                    $item = $new;
                }
            }

        }
        unset($file);

        $this->data['insert'] = $this->html->buildElement(
            [
                'type' => 'button',
                'text' => $this->language->get('text_add_file'),
                'href' => $this->html->getSecureURL('r/product/product/buildDownloadForm', '&product_id=' . $productId),

            ]
        );
        if ($this->config->get('config_embed_status')) {
            $this->data['embed_url'] = $this->html->getSecureURL('common/do_embed/product', '&product_id=' . $productId);
        }

        $this->view->assign('help_url', $this->gen_help_url('product_files'));
        $this->view->assign('form_language_switch', $this->html->getContentLanguageSwitcher());
        $this->view->batchAssign($this->data);
        $this->processTemplate('pages/catalog/product_files.tpl');

        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
    }

    protected function validateForm()
    {
        if (!$this->user->canModify('catalog/product_files')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->request->post['selected']) {
            $this->error['warning'] = $this->language->get('error_selected_downloads');
        }

        return (!$this->error);
    }

    public function pushToCustomers()
    {

        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $downloadId = (int)$this->request->get['download_id'];
        $productId = (int)$this->request->get['product_id'];

        $download_info = $this->download->getDownloadInfo($downloadId);

        if (!$download_info || !$productId) {
            abc_redirect($this->html->getSecureURL('catalog/product_files', '&product_id=' . $productId));
        }

        $download_info['attributes_data'] = serialize($this->download->getDownloadAttributesValues($downloadId));
        $this->loadModel('catalog/download');
        $orders_for_push = $this->model_catalog_download->getOrdersWithProduct($productId);
        $updated_array = [];
        if ($orders_for_push) {
            foreach ($orders_for_push as $row) {
                $updated_array = array_merge(
                    $updated_array,
                    $this->download->addUpdateOrderDownload($row['order_product_id'], $row['order_id'], $download_info)
                );
            }

            $this->loadLanguage('catalog/files');
            $this->session->data['success'] = sprintf(
                $this->language->get('success_push_to_orders'),
                count($updated_array)
            );
        }
        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);

        abc_redirect($this->html->getSecureURL('catalog/product_files', '&product_id=' . $productId));
    }
}