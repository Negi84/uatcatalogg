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
use abc\models\catalog\Product;

if (!class_exists('abc\core\ABC') || !ABC::env('IS_ADMIN')) {
    header('Location: static_pages/?forbidden=' . basename(__FILE__));
}

class ControllerPagesReportViewed extends AController
{
    public function main()
    {

        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $this->document->setTitle($this->language->get('heading_title'));
        $grid_settings = [
            //id of grid
            'table_id'       => 'report_viewed_grid',
            // url to load data from
            'url'            => $this->html->getSecureURL('listing_grid/report_viewed'),
            // default sort column
            'sortname'       => 'viewed',
            'columns_search' => false,
            'multiselect'    => 'false',
        ];

        $grid_settings['colNames'] = [
            $this->language->get('column_product_id'),
            $this->language->get('column_name'),
            $this->language->get('column_model'),
            $this->language->get('column_viewed'),
            $this->language->get('column_percent'),
        ];

        $grid_settings['colModel'] = [
            [
                'name'     => 'product_id',
                'index'    => 'product_id',
                'width'    => 50,
                'align'    => 'center',
                'sortable' => false,
            ],
            [
                'name'     => 'name',
                'index'    => 'name',
                'width'    => 300,
                'align'    => 'left',
                'sortable' => false,
            ],
            [
                'name'     => 'model',
                'index'    => 'model',
                'width'    => 80,
                'align'    => 'center',
                'sortable' => false,
            ],
            [
                'name'     => 'viewed',
                'index'    => 'viewed',
                'width'    => 50,
                'align'    => 'center',
                'sortable' => false,
            ],
            [
                'name'     => 'percent',
                'index'    => 'percent',
                'width'    => 50,
                'align'    => 'center',
                'sortable' => false,
            ],
        ];

        $grid = $this->dispatch('common/listing_grid', [$grid_settings]);
        $this->view->assign('listing_grid', $grid->dispatchGetOutput());

        $this->document->initBreadcrumb(
            [
                'href'      => $this->html->getSecureURL('index/home'),
                'text'      => $this->language->get('text_home'),
                'separator' => FALSE
            ]
        );

        $this->document->addBreadcrumb(
            [
                'href'      => $this->html->getSecureURL('report/viewed'),
                'text'      => $this->language->get('heading_title'),
                'separator' => ' :: ',
                'current'   => true
            ]
        );


        $this->view->assign('success', $this->session->data['success']);
        if (isset($this->session->data['success'])) {
            unset($this->session->data['success']);
        }

        $this->view->assign('reset', $this->html->getSecureURL('report/viewed/reset'));

        $this->processTemplate('pages/report/viewed.tpl');
        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
    }

    public function reset()
    {
        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        Product::where('viewed', '>', 0)->update(['viewed' => 0]);
        $this->cache->flush('product');

        $this->session->data['success'] = $this->language->get('text_success');

        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);

        $url = '';
        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }
        abc_redirect($this->html->getSecureURL('report/viewed', $url));
    }
}