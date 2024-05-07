<?php
/**
 * AbanteCart, Ideal Open Source Ecommerce Solution
 * http://www.abantecart.com
 *
 * Copyright 2011-2018 Belavier Commerce LLC
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

use abc\core\ABC;
use abc\core\engine\AController;
use abc\core\engine\Registry;
use abc\core\lib\APromotion;
use abc\core\engine\AResource;
use abc\models\catalog\Product;
use abc\modules\traits\ProductListingTrait;

/**
 * Class ControllerPagesProductSpecial
 *
 * @package abc\controllers\storefront
 * @property \abc\models\storefront\ModelCatalogReview $model_catalog_review
 */
class ControllerPagesProductFeatured extends AController
{
    use ProductListingTrait;

    public function __construct(Registry $registry, $instance_id, $controller, $parent_controller = '')
    {
        parent::__construct($registry, $instance_id, $controller, $parent_controller);
        $this->fillSortsList();
    }

    public function main()
    {

        $this->parsePaginationQueryParams($this->request->get);

        $this->data['search_parameters'] = [
            'start' => ($this->data['page'] - 1) * $this->data['limit'],
            'limit' => $this->data['limit'],
            'language_id' => $this->language->getLanguageID(),
            'sort'  => $this->data['sort'],
            'order' => $this->data['order']
        ];

        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        if ($this->config->get('config_require_customer_login') && !$this->customer->isLogged()) {
            abc_redirect($this->html->getSecureURL('account/login'));
        }

        $this->loadLanguage('product/featured');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->setBreadCrumbs($this->getSelfUrl('product/featured'));

        $productList = Product::getFeaturedProducts($this->data['search_parameters']);
        $productTotal = $productList::getFoundRowsCount();

        if ($productTotal) {
            //if single result, redirect to the product
            $this->forwardSingleResult($productList);
            $this->data['button_add_to_cart'] = $this->language->get('button_add_to_cart');
            $this->processList($productList);

            $this->data['display_price'] = ($this->config->get('config_customer_price') || $this->customer->isLogged());
            $this->data['review_status'] = $this->config->get('enable_reviews');

            $this->data['url'] = $this->html->getURL('product/featured');

            $this->setSortingSelector();
            $this->setPagination('product/featured', $productTotal);

            $this->view->setTemplate('pages/product/featured.tpl');
        } else {
            $this->data['text_error'] = $this->language->get('text_empty');
            $this->data['button_continue'] = $this->html->buildElement(
                [
                    'type'  => 'button',
                    'name'  => 'continue_button',
                    'text'  => $this->language->get('button_continue'),
                    'style' => 'button',
                ]
            );
            $this->data['continue'] = $this->html->getHomeURL();
            $this->view->setTemplate('pages/error/not_found.tpl');
        }

        $this->view->batchAssign($this->data);
        $this->processTemplate();

        //init controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
    }
}
