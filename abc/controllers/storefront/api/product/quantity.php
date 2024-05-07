<?php
/*------------------------------------------------------------------------------
  $Id$

  AbanteCart, Ideal OpenSource Ecommerce Solution
  http://www.AbanteCart.com

  Copyright 2011-2023 Belavier Commerce LLC

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

use abc\core\engine\AControllerAPI;
use abc\models\catalog\Product;

class ControllerApiProductQuantity extends AControllerAPI
{

    /**
     * @OA\Get (
     *     path="/index.php/?rt=a/product/quantity",
     *     summary="Get product quantity",
     *     description="Get quantity of products",
     *     tags={"Product"},
     *     security={{"apiKey":{}}},
     *     @OA\Parameter(
     *         name="product_id",
     *         in="query",
     *         required=true,
     *         description="Product unique Id",
     *        @OA\Schema(
     *              type="integer"
     *          ),
     *      ),
     *    @OA\Parameter(
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
     *         description="Quantity data",
     *         @OA\JsonContent(ref="#/components/schemas/QuantityResponseModel"),
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
        $this->extensions->hk_InitData($this, __FUNCTION__);
        $request = $this->rest->getRequestParams();
        $output = [];

        $productId = $request['product_id'];
        $optionValueId = $request['option_value_id'];

        if (!$productId || !is_numeric($productId)) {
            $this->rest->setResponseData([
                'error_code' => 400,
                'error_text' => 'Bad request',
            ]);
            $this->rest->sendResponse(400);
            return;
        }

        if (!$this->config->get('config_storefront_api_stock_check')) {
            $this->rest->setResponseData(
                [
                    'error_code' => 403,
                    'error_text' => 'Restricted access to stock check',
                ]
            );
            $this->rest->sendResponse(403);
            return;
        }

        //Load all the data from the model
        $product = Product::with('description', 'options', 'options.values')->find($productId);

        if (!$product) {
            $this->rest->setResponseData(
                [
                    'error_code' => 404,
                    'error_text' => 'Product not found',
                ]
            );
            $this->rest->sendResponse(404);
            return;
        }

        $output['stock_status_details'] = $product->stock_status->toArray();
        $output['stock_status'] = $product->stock_status->name;
        $output['quantity'] = max($product->quantity,0);

        foreach ($product->options as $option) {
            foreach ($option->values as $optionValue) {
                $output['option_value_quantities'][] = [
                    'product_option_value_id' => $optionValue->product_option_value_id,
                    'quantity'                => $optionValue->quantity,
                ];
            }
        }

        if (isset($optionValueId)) {
            //replace and return only option value quantity
            foreach ($output['option_value_quantities'] as $optionValue) {
                if ($optionValue['product_option_value_id'] == $optionValueId) {
                    $output = $optionValue;
                    $output['quantity'] = max(0,$output['quantity']);
                    break;
                }
            }
        }

        $this->extensions->hk_UpdateData($this, __FUNCTION__);

        $this->rest->setResponseData($output);
        $this->rest->sendResponse(200);
    }
}