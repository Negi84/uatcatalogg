<?php
/**
 * Json Input requered
 * {"catalog_id": 11, "quantity": 1,
 * "options"[
 *  {"product_option_value_id": 22}
 * ]}
 *
 *
 */
namespace abc\controllers\admin;

use abc\core\engine\AControllerAPI;
use abc\core\helper\AHelperUtils;
use abc\models\catalog\Product;
use abc\models\catalog\ProductOptionValue;

class ControllerApiCatalogPurchaseOrder extends AControllerAPI
{

    public function post()
    {
        $this->extensions->hk_InitData($this, __FUNCTION__);
        $request = $this->rest->getRequestParams();
        $response_arr = array();

        $json = htmlspecialchars_decode($request['json']);


        if (empty($json)) {
            $this->rest->setResponseData(array('Error' => 'Missing or incorrect input data'));
            $this->rest->sendResponse(200);
            return null;
        }

        if (!$this->config->get('config_storefront_api_stock_check')) {
            $this->rest->setResponseData(array('Error' => 'Restricted access to stock check '));
            $this->rest->sendResponse(200);
            return null;
        }


        $arJson = json_decode($json);

        $arResults = [];
        $i = 0;
        foreach ($arJson as $item) {
            $arProductInfo = $this->getProduct((int)$item->catalog_id);

            $arResults[$i]['product_id'] = (int)$item->catalog_id;
            $arResults[$i]['quantity'] = $arProductInfo['quantity'];
            $arResults[$i]['order_product_id'] = (int)$item->order_product_id;
            $arResults[$i]['need_quantity'] = (int)$item->quantity;
            $arResults[$i]['options'] = [];


            if (!empty($arProductInfo['option_value_quantities']) && !empty($item->options)) {

                $optionsValues = [];
                foreach ($arProductInfo['option_value_quantities'] as $option_value_quantity) {
                    foreach ($item->options as $option) {
                        if ($option_value_quantity['sku'] == $option->sku) {
                            $optionsValues[] = $option_value_quantity;
                        }
                    }
                }
                $arResults[$i]['options'] = $optionsValues;
            }
            $i++;
        }


        $arResponse = [];

        foreach ($arResults as $arResult) {
            $stock = true;
            //Check if same option quantity less that need
            foreach ($arResult['options'] as $option) {
                if ($option['quantity'] == 0) {
                    $stock = false;
                }
            }
            if (empty($arResult['options'])) {
                if ($arResult['quantity'] == 0) {
                    $stock = false;
                } else {
                    $stock = true;
                }
            }
            if (!$stock) {
                $response['catalog_id'] = $arResult['product_id'];
                $response['deducted'] = 0;
                $response['quantity'] = $arResult['need_quantity'];
                $response['order_product_id'] = $arResult['order_product_id'];
                $response['po_quantity'] = $arResult['need_quantity'];
                $arResponse[] = $response;
            } else {
                $product = Product::find($arResult['product_id']);
                if (empty($arResult['options'])) {
                    $response['catalog_id'] = $arResult['product_id'];
                    $response['quantity'] = $arResult['need_quantity'];
                    $response['order_product_id'] = $arResult['order_product_id'];

                    if ($product->quantity < $arResult['need_quantity']) {
                        $response['deducted'] = $product->quantity;
                        $response['po_quantity'] = $arResult['need_quantity'] - $product->quantity;
                        $product->quantity = 0;
                    } else {
                        $response['deducted'] = $arResult['need_quantity'];
                        $response['po_quantity'] = 0;
                        //https://abantecart.myjetbrains.com/youtrack/issue/TIMSS-238#focus=streamItem-4-2312.0-0
                        $product->quantity = 0; // $product->quantity - $arResult['need_quantity'];
                    }
                    $product->save();
                    $arResponse[] = $response;

                } else {
                    $min_quantity = 0;
                    foreach ($arResult['options'] as $option) {
                        if ($min_quantity == 0  || $min_quantity > $option['quantity']) {
                            $min_quantity = $option['quantity'];
                        }
                    }

                    $response['catalog_id'] = $arResult['product_id'];
                    $response['quantity'] = $arResult['need_quantity'];
                    $response['order_product_id'] = $arResult['order_product_id'];

                    if ($min_quantity < $arResult['need_quantity']) {
                        $response['deducted'] = $min_quantity;
                        $response['po_quantity'] = $arResult['need_quantity'] - $min_quantity;
                        foreach ($arResult['options'] as $option) {
                            $productOptionValue =  ProductOptionValue::find($option['product_option_value_id']);
                            $productOptionValue->quantity = 0;
                            $productOptionValue->save();
                        }
                    } else {
                        $response['deducted'] = $arResult['need_quantity'];
                        $response['po_quantity'] = 0;
                        foreach ($arResult['options'] as $option) {
                            $productOptionValue =  ProductOptionValue::find($option['product_option_value_id']);
                            //https://abantecart.myjetbrains.com/youtrack/issue/TIMSS-238#focus=streamItem-4-2312.0-0
                            $productOptionValue->quantity = $productOptionValue->quantity - $arResult['need_quantity'];
                            $productOptionValue->save();
                        }
                    }
                    $arResponse[] = $response;
                }
            }
        }

        $response_arr = $arResponse;
        $this->extensions->hk_UpdateData($this, __FUNCTION__);

        $this->rest->setResponseData($response_arr);
        $this->rest->sendResponse(200);
    }

    public function getProduct($product_id) {
        //Load all the data from the model
        $this->loadModel('catalog/product');
        $product_info = $this->model_catalog_product->getProduct($product_id);
        if (count($product_info) <= 0) {
            $this->rest->setResponseData(array('Error' => 'No product found'));
            $this->rest->sendResponse(200);
            return null;
        }
        //filter data and return only QTY for product and option values

        $response_arr['product_id'] = $product_id;
        $response_arr['quantity'] = $product_info['quantity'];
        $response_arr['stock_status'] = $product_info['stock_status'];
        if ($product_info['quantity'] <= 0) {
            $response_arr['quantity'] = 0;
        }

        $product_info['options'] = $this->model_catalog_product->getProductOptions($product_id);
        foreach ($product_info['options'] as $option) {
            foreach ($option['product_option_value'] as $option_val) {
                $response_arr['option_value_quantities'][] = array(
                    'product_option_value_id' => $option_val['product_option_value_id'],
                    'quantity'                => $option_val['quantity'],
                    'sku'                => $option_val['sku'],
                );
            }
        }

        if (isset($opt_val_id)) {
            //replace and return only option value quantity
            foreach ($response_arr['option_value_quantities'] as $option_val) {
                if ($option_val['product_option_value_id'] == $opt_val_id) {
                    $response_arr = $option_val;
                    if ($response_arr['quantity'] <= 0) {
                        $response_arr['quantity'] = 0;
                    }
                    break;
                }
            }
        }

        return $response_arr;
    }
}
