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

namespace abc\modules\traits;

use abc\core\ABC;
use abc\core\engine\AResource;
use abc\core\lib\AException;
use abc\models\catalog\Product;
use Illuminate\Support\Collection;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;
use stdClass;

/**
 * Trait ProductListingTrait
 *
 * @property \abc\core\engine\ALanguage $language
 *
 */
trait ProductListingTrait
{
    public function parsePaginationQueryParams(?array $request = [])
    {
        $request = $request ?: $this->request->get;
        $this->data['page'] = $request['page'] ?? 1;

        if (!$this->data['sorts']) {
            $this->fillSortsList();
        }

        $sorting_href = $request['sort'];
        if (!$sorting_href || !isset($this->data['sorts'][$request['sort']])) {
            $sorting_href = $this->config->get('config_product_default_sort_order');
        }
        $this->data['sorting_href'] = $sorting_href;
        list($this->data['sort'], $this->data['order']) = explode("-", $sorting_href);

        if (isset($request['limit'])) {
            $this->data['limit'] = (int)$request['limit'] ?: $this->config->get('config_catalog_limit');
            $this->data['limit'] = min($this->data['limit'], 50);
        } else {
            $this->data['limit'] = $this->config->get('config_catalog_limit');
        }
    }
    public function fillSortsList()
    {
        $default_sorting = $this->config->get('config_product_default_sort_order');
        $sort_prefix = '';
        if (str_starts_with($default_sorting, 'name-')) {
            $sort_prefix = 'pd.';
        } elseif (str_starts_with($default_sorting, 'price-')) {
            $sort_prefix = 'p.';
        }
        $this->data['sorts'] = [
            $sort_prefix . $default_sorting => $this->language->get('text_default'),
            'name-ASC'                      => $this->language->get('text_sorting_name_asc'),
            'name-DESC'                     => $this->language->get('text_sorting_name_desc'),
            'price-ASC'                     => $this->language->get('text_sorting_price_asc'),
            'price-DESC'                    => $this->language->get('text_sorting_price_desc'),
            'rating-DESC'                   => $this->language->get('text_sorting_rating_desc'),
            'rating-ASC'                    => $this->language->get('text_sorting_rating_asc'),
            'date_modified-DESC'            => $this->language->get('text_sorting_date_desc'),
            'date_modified-ASC'             => $this->language->get('text_sorting_date_asc'),
        ];
    }

    public function forwardSingleResult(Collection $productList)
    {
        if ($productList->count() == 1 && $this->data['page'] == 1) {
            abc_redirect(
                $this->html->getSEOURL(
                    'product/product',
                    '&product_id=' . $productList->first()->product_id,
                    '&encode')
            );
        }
    }

    /**
     * @param Collection|array $list
     * @param array|null $options
     * @return void
     * @throws AException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function processList(Collection|array $list, ?array $options = [])
    {
        $cart_rt = $this->config->get('embed_mode') ? 'r/checkout/cart/embed' : 'checkout/cart';

        if ($this->customer->isLogged() || $this->customer->isUnauthCustomer()) {
            $this->data['is_customer'] = true;
            $wishlist = $this->customer->getWishList();
        } else {
            $wishlist = [];
        }

        $productIds = $list->pluck('product_id')->toArray();

        //get thumbnails by one pass
        $resource = new AResource('image');
        $thumbnails = $resource->getMainThumbList(
            'products',
            $productIds,
            $options['image_width'] ?: $this->config->get('config_image_product_width'),
            $options['image_height'] ?: $this->config->get('config_image_product_height')
        );

        /** @var stdClass|Collection|Product $result */
        foreach ($list as $i => $result) {
            $thumbnail = $thumbnails[$result->product_id];
            $rating = $result->rating;
            $special = false;
            $discount = $result->discount_price;

            if ($discount) {
                $price = $this->currency->format(
                    $this->tax->calculate(
                        $discount,
                        $result->tax_class_id,
                        $this->config->get('config_tax')
                    )
                );
            } else {
                $price = $this->currency->format(
                    $this->tax->calculate(
                        $result['price'],
                        $result->tax_class_id,
                        $this->config->get('config_tax')
                    )
                );
                $special = $result['special_price'];
                if ($special) {
                    $special = $this->currency->format(
                        $this->tax->calculate(
                            $special,
                            $result->tax_class_id,
                            $this->config->get('config_tax')
                        )
                    );
                }
            }

            $hasOptions = $result->option_count;

            if ($hasOptions) {
                $add = $this->html->getSEOURL(
                    'product/product',
                    '&product_id=' . $result['product_id'],
                    '&encode'
                );
            } else {
                if ($this->config->get('config_cart_ajax')) {
                    $add = '#';
                } else {
                    $add = $this->html->getSecureURL(
                        $cart_rt,
                        '&product_id=' . $result['product_id'],
                        '&encode'
                    );
                }
            }
            //check for stock status, availability and config
            $track_stock = false;
            $in_stock = false;
            $no_stock_text = $this->language->get('text_out_of_stock');
            $total_quantity = 0;
            $stock_checkout = $result->stock_checkout === ''
                ? $this->config->get('config_stock_checkout')
                : $result->stock_checkout;
            if ($result->subtract) {
                $track_stock = true;
                $total_quantity = $result->quantity;
                //we have stock or out of stock checkout is allowed
                if ($total_quantity > 0 || $stock_checkout) {
                    $in_stock = true;
                }
            }

            $in_wishlist = false;
            if ($wishlist && $wishlist[$result->product_id]) {
                $in_wishlist = true;
            }

            $catalog_mode = false;
            if ($result->product_type_id) {
                $prodTypeSettings = Product::getProductTypeSettings((int)$result->product_id);

                if ($prodTypeSettings
                    && is_array($prodTypeSettings)
                    && isset($prodTypeSettings['catalog_mode'])
                ) {
                    $catalog_mode = (bool)$prodTypeSettings['catalog_mode'];
                }
            }


            $this->data['products'][$i] = $result->toArray();
            $this->data['products'][$i]['stars'] = sprintf($this->language->get('text_stars'), $rating);
            $this->data['products'][$i]['price'] = $price;
            $this->data['products'][$i]['options'] = $hasOptions;
            $this->data['products'][$i]['special'] = $special;
            $this->data['products'][$i]['thumb'] = $thumbnail;
            $this->data['products'][$i]['href'] = $this->html->getSEOURL(
                'product/product',
                '&product_id=' . $result->product_id,
                '&encode'
            );
            $this->data['products'][$i]['add'] = $add;
            $this->data['products'][$i]['track_stock'] = $track_stock;
            $this->data['products'][$i]['in_stock'] = $in_stock;
            $this->data['products'][$i]['no_stock_text'] = $no_stock_text;
            $this->data['products'][$i]['total_quantity'] = $total_quantity;
            $this->data['products'][$i]['in_wishlist'] = $in_wishlist;
            $this->data['products'][$i]['product_wishlist_add_url'] = $this->html->getURL(
                'product/wishlist/add',
                '&product_id=' . $result->product_id
            );
            $this->data['products'][$i]['product_wishlist_remove_url'] = $this->html->getURL(
                'product/wishlist/remove',
                '&product_id=' . $result->product_id
            );
            $this->data['products'][$i]['catalog_mode'] = $catalog_mode;
            $this->data['products'][$i]['description'] = html_entity_decode(
                $this->data['products'][$i]['description'],
                ENT_QUOTES,
                ABC::env('APP_CHARSET')
            );
        }
    }

    public function getSelfUrl(string $rt, ?array $paramNames = [], ?array $values = [])
    {
        $values = $values ?: $this->request->get;
        $paramNames = array_merge((array)$paramNames, ['sort', 'order', 'page', 'limit']);
        $queryString = '';

        foreach ($paramNames as $paramName) {
            $queryString .= $values[$paramName] ? '&' . $paramName . '=' . $values[$paramName] : '';
        }
        return $this->html->getSecureURL($rt, $queryString);
    }

    public function setBreadCrumbs(string $pageUrl, ?string $customText = '')
    {
        $this->document->resetBreadcrumbs();
        $this->document->addBreadcrumb(
            [
                'href'      => $this->html->getHomeURL(),
                'text'      => $this->language->get('text_home'),
                'separator' => false,
            ]
        );

        $this->document->addBreadcrumb(
            [
                'href'      => $pageUrl,
                'text'      => $customText ?: $this->language->get('heading_title'),
                'separator' => $this->language->get('text_separator'),
            ]
        );
    }

    public function setPagination(string $rt, int $total, ?array $queryParameters = [])
    {
        $this->data['pagination_bootstrap'] = $this->html->buildElement(
            [
                'type'       => 'Pagination',
                'name'       => 'pagination',
                'text'       => $this->language->get('text_pagination'),
                'text_limit' => $this->language->get('text_per_page'),
                'total'      => $total,
                'page'       => $this->data['page'],
                'limit'      => $this->data['limit'],
                'url'        => $this->html->getURL(
                    $rt,
                    ($queryParameters ? '&' . http_build_query($queryParameters) : '') .
                    '&sort=' . $this->data['sorting_href'] . '&page={page}' . '&limit=' . $this->data['limit'],
                    '&encode'
                ),
                'style'      => 'pagination',
            ]
        );
    }

    public function setSortingSelector()
    {
        $this->data['sorting'] = $this->html->buildElement(
            [
                'type'    => 'selectbox',
                'name'    => 'sort',
                'options' => $this->data['sorts'],
                'value'   => $this->data['sorting_href'],
            ]
        );
    }

}