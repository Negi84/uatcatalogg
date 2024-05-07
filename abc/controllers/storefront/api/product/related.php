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
use abc\core\engine\AControllerAPI;
use abc\core\engine\AResource;
use abc\models\catalog\Product;
use abc\models\QueryBuilder;
use stdClass;

/**
 * Class ControllerApiProductRelated
 *
 * @package abc\controllers\storefront
 */
class ControllerApiProductRelated extends AControllerAPI
{
    /**
     * @OA\Get (
     *     path="/index.php/?rt=a/product/related",
     *     summary="Get related products",
     *     description="Get kist of related products",
     *     tags={"Product"},
     *     security={{"apiKey":{}}},
     *    @OA\Parameter(
     *         name="product_id",
     *         in="query",
     *         required=true,
     *         description="Product Id",
     *        @OA\Schema(
     *              type="integer"
     *              type="array"
     *          ),
     *      ),
     *     @OA\Parameter(
     *         name="language_id",
     *         in="query",
     *         required=true,
     *         description="Language Id",
     *        @OA\Schema(
     *              type="integer"
     *          ),
     *      ),
     *      @OA\Parameter(
     *         name="store_id",
     *         in="query",
     *         required=true,
     *         description="Store Id",
     *     @OA\Schema(
     *              type="integer"
     *          ),
     *      ),
     *     @OA\Response(
     *         response="200",
     *         description="Products",
     *         @OA\JsonContent(ref="#/components/schemas/GetProductsModel"),
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad Request",
     *         @OA\JsonContent(ref="#/components/schemas/ApiErrorResponse"),
     *     ),
     *     @OA\Response(
     *         response="403",
     *         description="Access denied",
     *         @OA\JsonContent(ref="#/components/schemas/ApiErrorResponse"),
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(ref="#/components/schemas/ApiErrorResponse"),
     *     ),
     *      @OA\Response(
     *         response="500",
     *         description="Server Error",
     *         @OA\JsonContent(ref="#/components/schemas/ApiErrorResponse"),
     *     )
     * )
     *
     */
    public function get()
    {
        //TODO: Add support store_id and language_id, maybe currency
        //TODO: Change Error response to standard
        $this->data['search_parameters'] = [
            'with_all' => true,
        ];

        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        if ($this->config->get('config_require_customer_login') && !$this->customer->isLogged()) {
            $this->rest->setResponseData([
                'error_code' => 403,
                'error_text' => 'Access denied',
            ]);
            $this->rest->sendResponse(403);
            return;
        }

        $requestParams = $this->rest->getRequestParams();

        if (!$requestParams) {
            $this->rest->setResponseData(
                [
                    'error_code' => 406,
                    'error_text' => 'Missing one of required product filter parameters'
                ]
            );
            $this->rest->sendResponse(406);
            return;
        }

        // NOTE: product_id can be int or an array!
        $this->data['search_parameters']['filter']['related_to'] = $requestParams['product_id'];
        $this->data['search_parameters']['filter']['store_id'] = $requestParams['store_id']
            ?? $this->config->get('config_store_id');
        $this->data['search_parameters']['filter']['language_id'] = $requestParams['language_id']
            ?? $this->language->getLanguageID();

        if ($requestParams['category_id']) {
            $this->data['search_parameters']['filter']['category_id'] = [$requestParams['category_id']];
        }
        if ($requestParams['manufacturer_id']) {
            $this->data['search_parameters']['filter']['manufacturer_id'] = $requestParams['manufacturer_id'];
        }

        $this->data['search_parameters']['filter']['price_from'] = $requestParams['price_from'] ?? $requestParams['pFrom'];
        $this->data['search_parameters']['filter']['price_to'] = $requestParams['price_to'] ?? $requestParams['pTo'];

        $this->data['search_parameters']['sort'] = $requestParams['sort'];
        $this->data['search_parameters']['order'] = $requestParams['order'];
        $this->data['search_parameters']['start'] = $requestParams['start'];
        $this->data['search_parameters']['limit'] = $requestParams['limit'];

        $products = Product::getProducts($this->data['search_parameters']);

        /** @see QueryBuilder::get() */
        $total = $products::getFoundRowsCount();

        //get total
        $total_pages = $total ? ceil($total / $products->count()) : 0;

        //Preserved jqGrid JSON interface
        $response = new stdClass();
        $response->page = $requestParams['page'] ?: 1;
        $response->total = $total_pages;
        $response->records = $total;
        $response->limit = $requestParams['limit'];
        $response->sort = $requestParams['sort'];
        $response->order = $requestParams['order'];
        $response->params = $this->data['search_parameters'];

        $results = $products->toArray();

        $i = 0;
        if ($results) {
            $product_ids = array_column($results, 'product_id');
            $resource = new AResource('image');
            $thumbnails = $resource->getMainThumbList(
                'products',
                $product_ids,
                $this->config->get('config_image_thumb_width'),
                $this->config->get('config_image_thumb_height')
            );

            foreach ($results as $result) {
                $thumbnail = $thumbnails[$result['product_id']];
                $response->rows[$i] =
                    [
                        'id'   => $result['product_id'],
                        'cell' => array_merge(
                            $result,
                            [
                                'thumb'         => $thumbnail['thumb_url'],
                                'name'          => html_entity_decode($result['name'], ENT_QUOTES, ABC::env('APP_CHARSET')),
                                'description'   => html_entity_decode($result['description'], ENT_QUOTES, ABC::env('APP_CHARSET')),
                                'model'         => $result['model'],
                                'price'         => $this->currency->convert(
                                    $result['final_price'],
                                    $this->config->get('config_currency'),
                                    $this->currency->getCode()
                                ),
                                'currency_code' => $this->currency->getCode(),
                            ]
                        )
                    ];
                $i++;
            }
        }

        //init controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);

        $this->rest->setResponseData($response);
        $this->rest->sendResponse(200);
    }
}