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

use abc\core\ABC;
use abc\core\engine\AController;
use abc\core\engine\AResource;
use abc\core\engine\Registry;
use abc\core\lib\AException;
use abc\models\catalog\Category;
use abc\models\catalog\CategoryDescription;
use abc\models\catalog\Manufacturer;
use abc\models\catalog\Product;
use abc\modules\traits\ProductListingTrait;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;
use Illuminate\Support\Collection;

/**
 * Class ControllerPagesProductCategory
 *
 * @package abc\controllers\storefront
 */
class ControllerPagesProductCategory extends AController
{
    use ProductListingTrait;

    public function __construct(Registry $registry, $instance_id, $controller, $parent_controller = '')
    {
        parent::__construct($registry, $instance_id, $controller, $parent_controller);
        $this->fillSortsList();
    }

    public function main()
    {
        $request = $this->request->get;

        if (!$request['path'] && isset($request['category_id'])) {
            $request['path'] = $request['category_id'];
        }

        if (isset($request['path'])) {
            $path = '';
            $parts = explode('_', (string)$request['path']);
            $category_id = end($parts);
            if (count($parts) == 1) {
                $category = Category::find((int) $request['path']);
                if ($category) {
                    $parts = explode('_', (string)($category->path ? : $request['path']));
                }
            }

            $descriptions = CategoryDescription::select(['category_id', 'name'])
                ->whereIn('category_id', $parts)
                ->where('language_id', '=', $this->language->getLanguageID())
                ->get()
                ?->toArray();

            $categoryNames = array_column($descriptions, 'name', 'category_id');

            foreach ($parts as $path_id) {
                if ($categoryNames[$path_id]) {
                    if (!$path) {
                        $path = $path_id;
                    } else {
                        $path .= '_'.$path_id;
                    }

                    $this->document->addBreadcrumb(
                        [
                            'href'      => $this->html->getSEOURL(
                                'product/category',
                                '&path=' . $path, '&encode'
                            ),
                            'text'      => $categoryNames[$path_id],
                            'separator' => $this->language->get('text_separator'),
                        ]
                    );
                }
            }
        } else {
            $category_id = 0;
        }

        if ($category_id) {
            $categoryInfo = Category::getCategory($category_id);
            if (!$categoryInfo) {
                $this->processNotFound();
                return;
            }
            $title = $categoryInfo['name'];
            $this->data['category_info'] = $categoryInfo;
            $this->document->setKeywords($categoryInfo['meta_keywords']);
            $this->document->setDescription($categoryInfo['meta_description']);
            $this->data['description'] = html_entity_decode(
                $categoryInfo['description'],
                ENT_QUOTES,
                ABC::env('APP_CHARSET')
            );
        } else {
            //Display Top category when embed mode or have PATH parameter
            $title = $this->language->get('text_top_category');
            $categoryInfo = [];
        }

        $this->parsePaginationQueryParams($this->request->get);

        //allow to change from hooks
        $this->data['products_search_parameters'] = [
            'filter'              => [
                'category_id' => $category_id,
                'only_enabled' => true
            ],
            'with_all' => true,
            'start'    => ($this->data['page'] - 1) * $this->data['limit'],
            'limit'    => $this->data['limit'],
            'sort'     => $this->data['sort'],
            'order'    => $this->data['order'],
        ];

        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        if ($this->config->get('config_require_customer_login') && !$this->customer->isLogged()) {
            abc_redirect($this->html->getSecureURL('account/login'));
        }

        $this->loadLanguage('product/category');

        $this->document->setTitle($title);
        $this->data['heading_title'] = $title;

        $categories = Category::getCategories($category_id);

        /** @see Product::getProducts() */
        $productList = Product::search($this->data['products_search_parameters']);
        /** @see QueryBuilder::get() */
        $productTotal = $productList::getFoundRowsCount();

        if ($categories || $productTotal) {
            if ($categories) {
                $this->prepareCategoryList($categories, ['sort' => $this->data['sort'], 'order' => $this->data['order']]);
            }
            if ($productTotal) {
                $this->data['button_add_to_cart'] = $this->language->get('button_add_to_cart');
                $this->processList($productList);

                $this->data['display_price'] = ($this->config->get('config_customer_price') || $this->customer->isLogged());
                $this->data['review_status'] = $this->config->get('enable_reviews');

                $this->data['url'] = $this->html->getSEOURL('product/category', '&path=' . $path);

                $this->setSortingSelector();
                $this->setPagination('product/category', $productTotal, ['path' => $path]);

                $this->view->setTemplate('pages/product/category.tpl');
            }
        } else {
            $this->document->setTitle($title);
            $this->document->setDescription($categoryInfo['meta_description']);
            $this->data['heading_title'] = $categoryInfo['name'];
            $this->data['button_continue'] = $this->language->get('button_continue');
            $this->data['continue'] = $this->html->getHomeURL();
            $this->data['categories'] = $this->data['products'] = [];
        }

        $this->view->batchAssign($this->data);
        $this->processTemplate();

        //init controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
    }

    protected function prepareCategoryList($categories, $urlParams)
    {
        $category_ids = array_map('intval', array_column($categories, 'category_id'));

        //get thumbnails by one pass
        $resource = new AResource('image');
        $thumbnails = $resource->getMainThumbList(
            'categories',
            $category_ids,
            $this->config->get('config_image_category_width'),
            $this->config->get('config_image_category_height')
        );
        $this->data['categories'] = [];
        $pathPrefix = $this->request->get['path'];
        $pathPrefix = $pathPrefix ? $pathPrefix . '_' : '';
        foreach ($categories as $result) {
            $thumbnail = $thumbnails[$result['category_id']];
            $this->data['categories'][] = [
                'name'  => $result['name'],
                'href'  => $this->getSelfUrl(
                    'product/category',
                    ['path'],
                    [
                        'path'  => $pathPrefix . $result['category_id'],
                        'sort'  => $this->data['sort'],
                        'order' => $this->data['order'],
                        'page'  => $this->data['page'],
                        'limit' => $this->data['limit'],
                    ]
                ),
                'thumb' => $thumbnail,
            ];
        }
    }
    protected function processNotFound()
    {
        $this->document->setTitle($this->language->get('text_error'));
        $this->data['heading_title'] = $this->language->get('text_error');
        $this->data['text_error'] = $this->language->get('text_error');
        $this->data['button_continue'] = $this->html->buildElement(
                [
                    'type'  => 'button',
                    'name'  => 'continue_button',
                    'text'  => $this->language->get('button_continue'),
                    'style' => 'button',
                ]
        );
        $this->data['continue'] = $this->html->getHomeURL();
        $this->view->batchAssign($this->data);
        $this->view->setTemplate('pages/error/not_found.tpl');
        $this->processTemplate();
    }
}