<?php
/*------------------------------------------------------------------------------
  $Id$

  AbanteCart, Ideal OpenSource Ecommerce Solution
  http://www.AbanteCart.com

  Copyright © 2011-2022 Belavier Commerce LLC

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
use abc\core\engine\AResource;
use abc\core\engine\Registry;
use abc\models\catalog\Manufacturer;
use abc\models\catalog\Product;
use abc\modules\traits\ProductListingTrait;
use Illuminate\Support\Collection;
use stdClass;

/**
 * Class ControllerPagesProductManufacturer
 *
 * @package abc\controllers\storefront
 *
 */
class ControllerPagesProductManufacturer extends AController
{
    use ProductListingTrait;

    public function __construct(Registry $registry, $instance_id, $controller, $parent_controller = '')
    {
        parent::__construct($registry, $instance_id, $controller, $parent_controller);
        $this->fillSortsList();
    }

    public function main()
    {
        $manufacturerId = $this->request->get['manufacturer_id'];

        $this->parsePaginationQueryParams($this->request->get);

        $this->data['search_parameters'] = [
            'with_all'    => true,
            'start'       => ($this->data['page'] - 1) * $this->data['limit'],
            'limit'       => $this->data['limit'],
            'language_id' => $this->language->getLanguageID(),
            'sort'        => $this->data['sort'],
            'order'       => $this->data['order'],
            'filter'      => [
                'manufacturer_id' => $manufacturerId
            ]
        ];

        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        if ($this->config->get('config_require_customer_login') && !$this->customer->isLogged()) {
            abc_redirect($this->html->getSecureURL('account/login'));
        }

        $this->loadLanguage('product/manufacturer');
        $manufacturerInfo = Manufacturer::find($manufacturerId);
        if (!$manufacturerInfo) {
            abc_redirect($this->html->getHomeURL());
        }

        $title = $manufacturerInfo->name;

        $this->document->setTitle($title);
        $this->data['heading_title'] = $title;

        $this->setBreadCrumbs(
            $this->getSelfUrl(
                'product/manufacturer',
                ['manufacturer_id'],
                ['manufacturer_id' => $manufacturerId]
            ),
            $title
        );

        /** @see Product::getProducts() */
        $productList = Product::search($this->data['search_parameters']);
        /** @see QueryBuilder::get() */
        $productTotal = $productList::getFoundRowsCount();

        if ($productTotal) {
            //if single result, redirect to the product
            $this->forwardSingleResult($productList);
            $this->data['button_add_to_cart'] = $this->language->get('button_add_to_cart');
            $this->processList($productList);

            $this->data['display_price'] = ($this->config->get('config_customer_price') || $this->customer->isLogged());
            $this->data['review_status'] = $this->config->get('enable_reviews');

            $this->data['url'] = $this->html->getURL('product/manufacturer', '&manufacturer_id=' . $manufacturerId);

            $this->setSortingSelector();
            $this->setPagination('product/manufacturer', $productTotal, ['manufacturer_id' => $manufacturerId]);

            $this->view->setTemplate('pages/product/manufacturer.tpl');
        } else {
            $this->document->setTitle($title);
            $this->data['heading_title'] = $title;
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