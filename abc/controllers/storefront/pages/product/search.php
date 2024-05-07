<?php
/*------------------------------------------------------------------------------
  $Id$

  AbanteCart, Ideal OpenSource Ecommerce Solution
  http://www.AbanteCart.com

  Copyright © 2011-2018 Belavier Commerce LLC

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

use abc\core\ABC;
use abc\core\engine\AController;
use abc\core\engine\Registry;
use abc\core\lib\APromotion;
use abc\core\engine\AResource;
use abc\models\catalog\Category;
use abc\models\catalog\Product;
use abc\models\storefront\ModelCatalogReview;
use abc\modules\traits\ProductListingTrait;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class ControllerPagesProductSearch
 *
 * @package abc\controllers\storefront
 */
class ControllerPagesProductSearch extends AController
{
    protected $category;
    protected $path;

    use ProductListingTrait;

    public function __construct(Registry $registry, $instance_id, $controller, $parent_controller = '')
    {
        parent::__construct($registry, $instance_id, $controller, $parent_controller);
        $this->fillSortsList();
    }

    public function main()
    {
        $request = array_merge($this->request->get, $this->request->post);
        $this->path = explode(',', (string)$request['category_id']);

        if (isset($request['category_id'])) {
            $category_id = explode(',', (string)$request['category_id']);
            end($category_id);
            $category_id = current($category_id);
        } else {
            $category_id = '';
        }

        $this->parsePaginationQueryParams($request);

        $searchBy = [];
        if ($request['description']) {
            $searchBy[] = 'description';
        }
        if ($request['model']) {
            $searchBy[] = 'model';
        }
        if ($request['sku']) {
            $searchBy[] = 'sku';
        }
        $this->data['search_parameters'] =
            [
                'with_all' => true,
                'filter'              => [
                    'keyword'                   => $request['keyword'],
                    'keyword_search_parameters' => [
                        'search_by' => $searchBy,
                    ],
                    'category_id'               => $category_id,
                    'price_from'                => $request['price_from'],
                    'price_to'                  => $request['price_to'],
                ],
                'sort'     => $this->data['sort'],
                'order'    => $this->data['order'],
                'start'    => ($this->data['page'] - 1) * $this->data['limit'],
                'limit'    => $this->data['limit'],
            ];

        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $this->loadLanguage('product/search');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->setBreadCrumbs(
            $this->getSelfUrl('product/search', ['keyword', 'category_id', 'description', 'model'], $request)
        );

        $this->buildForm($request);


        if (isset($request['keyword'])) {
            /** @see Product::getProducts() */
            $productList = Product::search($this->data['search_parameters']);
            $productTotal = $productList::getFoundRowsCount();
            if ($productTotal) {
                //if single result, redirect to the product
                $this->forwardSingleResult($productList);
                $this->data['button_add_to_cart'] = $this->language->get('button_add_to_cart');
                $this->processList($productList);

                $this->data['display_price'] = ($this->config->get('config_customer_price') || $this->customer->isLogged());
                $this->data['review_status'] = $this->config->get('enable_reviews');

                $this->data['url'] = $this->html->getURL('product/search');

                $this->setSortingSelector();
                $this->setPagination('product/search', $productTotal, $request);
            }
        }
        $this->data['review_status'] = $this->config->get('enable_reviews');

        $this->view->batchAssign($this->data);
        $this->processTemplate('pages/product/search.tpl');
        //init controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
    }

    protected function getCategories($parent_id, $level = 0)
    {
        $level++;
        $data = [];
        $cat_id = explode(',', $parent_id);
        end($cat_id);
        $results = Category::getCategories(current($cat_id));

        foreach ($results as $result) {
            if (in_array($result['category_id'], $this->path)) {
                $this->category = $result['category_id'];
            } else {
                $this->category = 0;
            }

            $data[] = [
                'category_id' => $parent_id.','.$result['category_id'],
                'name'        => str_repeat('&nbsp;&nbsp;&nbsp;', $level).$result['name'],
            ];
            $children = [];
            if ($this->category) {
                $children = $this->getCategories($parent_id.','.$result['category_id'], $level);
            }

            if ($children) {
                $data = array_merge($data, $children);
            }
            unset($children);
        }

        return $data;
    }

    protected function buildForm($inData)
    {
        $this->data['keyword'] = $this->html->buildElement(
            [
                'type'  => 'input',
                'name'  => 'keyword',
                'value' => $inData['keyword'],
            ]
        );

        $options = [0 => $this->language->get('text_category')];
        Category::setCurrentLanguageID(Registry::language()->getLanguageID());
        $results = Category::getCategories(0, $this->config->get('store_id'));
        $options = $options + array_column($results, 'name', 'category_id');
        $this->data['category'] = $this->html->buildElement(
            [
                'type'    => 'selectbox',
                'name'    => 'category_id',
                'options' => $options,
                'value'   => $inData['category_id'],
            ]
        );

        $this->data['description'] = $this->html->buildElement(
            [
                'type'       => 'checkbox',
                'id'         => 'description',
                'name'       => 'description',
                'checked'    => (int)$inData['description'],
                'value'      => 1,
                'label_text' => $this->language->get('entry_description'),
            ]
        );

        $this->data['model'] = $this->html->buildElement(
            [
                'type'       => 'checkbox',
                'id'         => 'model',
                'name'       => 'model',
                'checked'    => (bool)$inData['model'],
                'value'      => 1,
                'label_text' => $this->language->get('entry_model'),
            ]
        );

        $this->data['submit'] = $this->html->buildElement([
            'type'  => 'button',
            'name'  => 'search_button',
            'text'  => $this->language->get('button_search'),
            'icon'  => 'fa fa-search',
            'style' => 'btn-default',
        ]);
    }
}
