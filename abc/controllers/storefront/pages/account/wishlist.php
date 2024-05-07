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

namespace abc\controllers\storefront;

use abc\core\engine\AController;
use abc\core\engine\HtmlElementFactory;
use abc\models\catalog\Product;
use abc\modules\traits\ProductListingTrait;
use H;

class ControllerPagesAccountWishlist extends AController
{
    use ProductListingTrait;
    public function main()
    {
        $this->parsePaginationQueryParams($this->request->get);

        $this->data['search_parameters'] = [
            'with_final_price'  => true,
            'with_option_count' => true,
            'start'             => ($this->data['page'] - 1) * $this->data['limit'],
            'limit'             => $this->data['limit'],
            'sort'              => $this->data['sort'],
            'order'             => $this->data['order']
        ];

        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->html->getSecureURL('account/wishlist');
            abc_redirect($this->html->getSecureURL('account/login'));
        }

        $this->document->setTitle($this->language->get('heading_title'));

        $this->getWishList();

        //init controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
        unset($this->session->data['success']);
    }

    private function getWishList()
    {

        $this->document->setTitle($this->language->get('heading_title'));

        $this->document->resetBreadcrumbs();
        $this->document->addBreadcrumb(
            [
                'href' => $this->html->getHomeURL(),
                'text' => $this->language->get('text_home'),
                'separator' => false,
            ]
        );
        $this->document->addBreadcrumb(
            [
                'href' => $this->html->getSecureURL('account/account'),
                'text' => $this->language->get('text_account'),
                'separator' => $this->language->get('text_separator'),
            ]
        );
        $this->document->addBreadcrumb(
            [
                'href' => $this->html->getSecureURL('account/wishlist'),
                'text' => $this->language->get('heading_title'),
                'separator' => $this->language->get('text_separator'),
            ]
        );

        $wishList = $this->customer->getWishList();

        if ($wishList && count($wishList) > 0) {
            $this->data['search_parameters']['filter']['include'] = array_keys($wishList);
            $productsList = Product::search($this->data['search_parameters']);
            if ($productsList) {
                $this->processList(
                    $productsList,
                    [
                        'image_width'  => $this->config->get('config_image_cart_width'),
                        'image_height' => $this->config->get('config_image_cart_height')
                    ]
                );
                foreach ($this->data['products'] as &$product) {
                    $product['added'] = H::dateInt2Display($wishList[$product['product_id']]);
                }
            }

            if (isset($this->session->data['redirect'])) {
                $this->data['continue'] = str_replace('&amp;', '&', $this->session->data['redirect']);
                unset($this->session->data['redirect']);
            } else {
                $this->data['continue'] = $this->html->getHomeURL();
            }

            $this->view->assign('error', '');
            if ($this->session->data['error']) {
                $this->view->assign('error', $this->session->data['error']);
                unset($this->session->data['error']);
            }

            $this->view->setTemplate('pages/account/wishlist.tpl');
        } else {
            $this->data['heading_title'] = $this->language->get('heading_title');
            $this->data['text_error'] = $this->language->get('text_empty_wishlist');

            $this->data['button_continue'] = HtmlElementFactory::create(
                [
                    'name' => 'continue',
                    'type' => 'button',
                    'text' => $this->language->get('button_continue'),
                    'href' => $this->html->getHomeURL(),
                    'style' => 'button',
                ]
            );

            $this->view->setTemplate('pages/error/not_found.tpl');
        }

        $cart_rt = $this->config->get('embed_mode')
            ? 'r/checkout/cart/embed'
            : 'checkout/cart';

        $this->data['cart'] = $this->html->getSecureURL($cart_rt);

        $this->view->batchAssign($this->data);
        $this->processTemplate();
    }
}