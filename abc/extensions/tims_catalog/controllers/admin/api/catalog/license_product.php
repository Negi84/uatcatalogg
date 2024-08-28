<?php

namespace abc\controllers\admin;

use abc\core\engine\AControllerAPI;
use abc\core\engine\Registry;
use abc\extensions\licensing\models\storefront\extension\ModelExtensionLicensing;
use abc\models\catalog\Product;
use abc\models\order\OrderProduct;

/**
 * Class ControllerApiCatalogLicenseProduct
 *
 * @package abc\controllers\admin
 */
class ControllerApiCatalogLicenseProduct extends AControllerAPI
{
    /**
     *
     */
    const DEFAULT_STATUS = 1;

    /**
     * @void
     */
    public function get()
    {
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $request = $this->rest->getRequestParams();
        $response_arr = [];
        if ($request['operation'] == 'getStock') {
            $response_arr = $this->getStock($request);
        } elseif ($request['operation'] == 'retrieveKeys') {
            $response_arr = $this->retrieveKeys($request);
        } elseif ($request['operation'] == 'revokeKeysByOrderProduct') {
            $response_arr = $this->revokeKeysByOrderProduct($request);
        } elseif ($request['operation'] == 'revokeKeysByOrder') {
            $response_arr = $this->revokeKeysByOrder($request);
        }

        $this->extensions->hk_UpdateData($this, __FUNCTION__);

        $this->rest->setResponseData($response_arr);
        $this->rest->sendResponse(200);
    }

    public function retrieveKeys($input)
    {
        if (!$input) {
            return [];
        }
        $output = [];
        foreach ($input['products'] as $item) {
            $totalCount = $item['total_count'];
            $opId = $item['order_product_id'];
            $output[$opId]['order_product_id'] = $opId;
            $availableLicenses = $this->getAvailableKeys('product_id', $item['product_id'], $item['option_value_sku']);
            if (!$availableLicenses) {
                $output[$opId]['error'] = 'No E-voucher keys found';
                Registry::messages()->saveNotice(
                    'API Request for license keys of product ID #'.$item['product_id'],
                    'Remote host asks License Keys of product #'.$item['product_id'].' but available keys not found (option sku "'.$item['option_value_sku'].'")'
                    );
            } elseif ($item['quantity'] > count((array)$availableLicenses[$item['option_value_sku']])) {
                $output[$opId]['error'] = 'No stock. '
                    .$item['quantity'].' needed, '
                    .count((array)$availableLicenses[$item['option_value_sku']]).' available (sku is '.$item['option_value_sku'].')';

                Registry::messages()->saveNotice(
                'API Request for license keys of product ID #'.$item['product_id'],
                'Remote host asks License Keys of product #'.$item['product_id'].' but '.$output[$opId]['error'].' (option sku "'.$item['option_value_sku'].'")'
                );
            } else {
                //if all fine - mark keys as purchased
                $k = 1;
                $sumQuantity = $this->db->table('licenses')
                            ->where('order_id',  $input['order_id'])
                            ->sum('quantity');
                            
                if($totalCount != $sumQuantity){
                    foreach ($availableLicenses as $sku => $r) {
                        foreach ($r as $row) {
                            if ($k > $item['quantity']) {
                                break;
                            }
                            $this->db->table('licenses')
                                     ->where('license_id', $row['license_id'])
                                     ->update(
                                [
                                    'order_id'         => $input['order_id'],
                                    'order_product_id' => $opId,
                                    'po_number'        => $input['po_number'],
                                    'site_alias'       => $input['site_alias'],
                                    'option_name'      => $item['option_name'],
                                ]
                            );
                            $k++;
                            $output[$opId]['license_keys'][] = $row;
                        }
                    }
                }
            }
        }
        return $output;
    }

    public function revokeKeysByOrder($input) {
        if (!$input) {
            return [];
        }
        $output = [];
        $orderId = (int)$input['order_id'];

        $this->db = $this->registry->get('db');
        $this->db->table('licenses')
            ->where('order_id', $orderId)
            ->update([
                'order_id'         => 0,
                'po_number'        => '',
                'site_alias'       => '',
                'order_product_id' => 0,
                'option_name'      => '',
            ]);
        return $output;
    }

    public function revokeKeysByOrderProduct($input) {
        if (!$input) {
            return [];
        }

        $orderId = (int)$input['order_id'];
        $orderProductId = (int)$input['order_product_id'];

        $this->db = $this->registry->get('db');
        $this->db->table('licenses')
            ->where(
                [
                    'order_id' => $orderId,
                    'order_product_id' => $orderProductId,
                ]
            )
            ->update([
                'order_id'         => 0,
                'po_number'        => '',
                'site_alias'       => '',
                'order_product_id' => 0,
                'option_name'      => ''
            ]);
        return ['result' => true];
    }

    public function getStock($input)
    {
        $getBy = null;
        if (isset($input['product_id']) && $input['product_id']) {
            $getBy = 'product_id';
        }
        if (isset($input['get_by']) && $input['get_by']) {
            $getBy = $input['get_by'];
        }

        if (!\H::has_value($getBy) || !isset($input[$getBy])) {
            $this->rest->setResponseData(['Error' => $getBy.' is missing']);
            $this->rest->sendResponse(200);
            return null;
        }

        $output = ['in_stock' => 0];

        $availableLicenses = $this->getAvailableKeys($getBy, $input[$getBy], $input['option_value_sku']);

        $output['in_stock'] = count((array)$availableLicenses[$input['option_value_sku']]);

        if (!$output) {
            $output = ['Error' => 'Requested Product Not Found'];
        }
        return $output;
    }

    protected function getAvailableKeys($getBy, $getByValue, $option_value_sku = '')
    {
        /**
         * @var Product $product
         */
        $product = null;
        try {
            $product = Product::where([$getBy => $getByValue])->first();
        } catch (\PDOException $e) {
        }

        if ($product === null) {
            return [];
        }

        /**
         * @var ModelExtensionLicensing $mdl
         *
         */
        $mdl = $this->loadModel('extension/licensing', 'storefront');
        $productLicences = $mdl->getLicenses($product->product_id);

        $availableLicenses = [];
        foreach ($productLicences as $row) {
            //skip already used or disabled licenses
            if ($row['order_id'] || !$row['status']) {
                continue;
            }
            //filter only available licenses with sku if given
            if ($option_value_sku && $row['sku'] != $option_value_sku) {
                continue;
            }

            $availableLicenses[$row['sku']][] = $row;
        }
        return $availableLicenses;
    }

}
