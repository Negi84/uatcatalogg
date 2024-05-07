<?php

namespace abc\extensions\licensing\models\admin\catalog;

use abc\core\engine\Model;
use abc\core\engine\Registry;
use abc\core\lib\ADB;
use abc\core\lib\AException;
use abc\models\admin\ModelCatalogProduct;
use abc\models\catalog\Product;
use abc\models\catalog\ProductOptionValueDescription;
use abc\models\order\OrderHistory;
use abc\modules\events\ABaseEvent;
use Exception;
use H;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;

/**
 * Class ModelCatalogLicensing
 *
 * @property ModelCatalogProduct $model_catalog_product
 * @property ADB $db
 */
class ModelCatalogLicensing extends Model
{

    public $error = [];

    /**
     * @param int $product_id
     * @param array $data
     *
     * @return false|array
     * @throws Exception|InvalidArgumentException
     */
    public function getTotalLicenses($product_id, $data)
    {
        return $this->getLicenses($product_id, $data, 'total_only');
    }

    /**
     * @param int $product_id
     * @param array $data
     * @param string $mode
     *
     * @return array|false|int
     * @throws Exception|InvalidArgumentException
     */
    public function getLicenses($product_id, $data, $mode = '')
    {

        $product_id = (int)$product_id;
        if (!$product_id) {
            return $mode == 'total_only' ? 0 : [];
        }

        if ($data) {
            if ($mode == 'total_only') {
                $total_sql = 'count(*) as total';
            } else {
                $total_sql = 'l.*, oo.name as option_value_name, ov.sku as option_value_sku';
            }
            $sql = "SELECT " . $total_sql . " 
                    FROM " . $this->db->table_name("licenses") . " l
                    LEFT JOIN " . $this->db->table_name("product_option_values") . " ov
                        ON ov.product_option_value_id = l.product_option_value_id
                    LEFT JOIN " . $this->db->table_name("product_option_value_descriptions") . " oo
                        ON (oo.product_option_value_id = l.product_option_value_id
                            AND oo.language_id = '" . $this->language->getLanguageID() . "' 
                            AND oo.product_id = ".$product_id.") 
                    WHERE l.product_id = ".$product_id;

            if (!empty($data['subsql_filter'])) {
                $sql .= " AND ".$data['subsql_filter'];
            }

            if ($mode == 'total_only') {
                $query = $this->db->query($sql);
                return $query->row['total'];
            }

            $sort_data = [
                'licence_key'       => 'licence_key',
                'date_modified'     => 'date_modified',
                'order_id'          => 'order_id',
                'option_value_name' => 'oo.name',
            ];

            if (isset($data['sort']) && in_array($data['sort'], array_keys($sort_data))) {
                $sql .= " ORDER BY ".$data['sort'];
            } else {
                $sql .= " ORDER BY date_modified ";
            }

            if (isset($data['order']) && ($data['order'] == 'DESC')) {
                $sql .= " DESC";
            } else {
                $sql .= " ASC";
            }

            if (isset($data['start']) || isset($data['limit'])) {
                if ($data['start'] < 0) {
                    $data['start'] = 0;
                }
                if ($data['limit'] < 1) {
                    $data['limit'] = 20;
                }
                $sql .= " LIMIT ".(int)$data['start'].",".(int)$data['limit'];
            }

            $query = $this->db->query($sql);
            return $query->rows;
        } else {
            // this slice of code is duplicate of storefront model for manufacturer
            $cache_key = 'product.licenses_' . $product_id;
            $licence_data = $this->cache->get($cache_key);
            if ($licence_data === false) {
                $query = $this->db->query("SELECT *
                                            FROM ".$this->db->table_name("licences")."
                                            WHERE product_id = '".$product_id."'
                                            ORDER BY date_modified DESC");
                $licence_data = $query->rows;
                $this->cache->put($cache_key, $licence_data);
            }

            return $licence_data;
        }
    }

    /**
     * @param $license_id
     *
     * @throws Exception
     */
    public function deleteLicense($license_id)
    {
        $license_id = (int)$license_id;
        $sql = "DELETE FROM ".$this->db->table_name('licenses')."  
                WHERE license_id=".$license_id." AND order_id<1";
        $this->db->query($sql);
    }

    /**
     * @param int $orderId
     */
    public function revokeLicenseByOrder($orderId) {
        $orderId = (int)$orderId;
        $this->db = $this->registry->get('db');
        $this->db->table('licenses')
            ->where('order_id', $orderId)
            ->update(
                [
                    'order_id'         => 0,
                    'po_number'        => '',
                    'site_alias'       => '',
                    'order_product_id' => 0
                ]
            );
    }

    /**
     * @param int $license_id
     * @param array $data
     *
     * @throws Exception
     */
    public function editLicense($license_id, $data)
    {

        $fields = ['status'];
        $update = [];

        foreach ($fields as $f) {
            if (isset($data[$f])) {
                $update[] = $f." = '".$this->db->escape($data[$f])."'";
            }
        }
        if (!empty($update)) {
            $sql = "UPDATE ".$this->db->table_name("licenses")." 
                    SET ".implode(',', $update)."  
                    WHERE license_id = '".(int)$license_id."'";
            $this->db->query($sql);
        }

        $this->cache->flush('product');
    }

    /**
     * @param int $product_id
     *
     * @return bool
     */
    public function setProductAsLicensed($product_id = 0)
    {
        $product_id = (int)$product_id;
        if (!$product_id) {
            return false;
        }

        $fields = [
            'featured'          => 0,
            'license'           => 1,
            'shipping'          => 0,
            'ship_individually' => 0,
            'free_shipping'     => 0,
            'shipping_price'    => 0.0,
            'call_to_order'     => 0,
            'weight'            => 0.0,
            'length'            => 0.0,
            'width'             => 0.0,
            'height'            => 0.0,
            'subtract'          => 0,
            'quantity'          => 0,
        ];

        $product = Product::find($product_id);
        if($product){
            $product->update($fields);
            $this->cache->flush('product');
            return true;
        }
        return false;
    }

    /**
     * @param int $product_id
     *
     * @return bool
     * @throws Exception
     */
    public function setProductAsGeneric($product_id = 0)
    {
        $product = Product::find($product_id);
        if($product){
            $product->update(
                [
                   'license' => 0,
                ]
            );
            $this->db->query(
                "DELETE FROM " . $this->db->table_name("licenses") . "
                 WHERE product_id = '" . (int)$product_id . "' 
                    AND COALESCE(order_id,0) < 1");
            $this->cache->flush('product');
            return true;
        }
        return false;
    }

    /**
     * @param int $product_id
     * @param string $external_url
     *
     * @return bool
     */
    public function setProductAsCatalogOnly($product_id = 0, $external_url = '')
    {
        $product_id = (int)$product_id;
        if (!$product_id) {
            return false;
        }

        $fields = [
            'featured'          => 0,
            'license'           => 0,
            'shipping'          => 0,
            'ship_individually' => 0,
            'free_shipping'     => 0,
            'shipping_price'    => 0.0,
            'call_to_order'     => 0,
            'weight'            => 0.0,
            'length'            => 0.0,
            'width'             => 0.0,
            'height'            => 0.0,
            'subtract'          => 0,
            'quantity'          => 0,
            'product_type'      => 'S', //Catalog only type - read as Show only
            'external_url'      => $external_url ?: ''
        ];

        $product = Product::find($product_id);
        if($product) {
            $product->update($fields);
            $this->cache->flush('product');
            return true;
        }

        return false;
    }

    /**
     * @param $filepath
     * @param $product_id
     * @param $product_option_value_id
     *
     * @return int
     * @throws AException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function importLicenses($filepath, $product_id, $product_option_value_id)
    {
        $product_id = (int)$product_id;
        $product_option_value_id = (int)$product_option_value_id;

        $licenses = file($filepath, FILE_SKIP_EMPTY_LINES);
        if (!$licenses || !$product_id || !$product_option_value_id) {
            return 0;
        }
        $count = 0;
        foreach ($licenses as $key) {
            $row = str_getcsv($key);
            /*
             * 0 - license code or url
             * 1 - expiry date
             * 2 - additional settings
             *
             */
            $eCode = $row[0];
            $eCode = trim($eCode);

            if (!$eCode) {
                continue;
            }

            if (str_starts_with($eCode, 'http')) {
                $eUrl = $eCode;
                $eCode = '';
            } else {
                $eUrl = '';
                $eCode = preg_replace('/[^A-Za-z0-9\-]/', '', $eCode);
            }

            $expiryDate = $row[1];
            $settings = $row[2];

            if ( //skip title row if exists
                strtolower($eCode) == 'code'
                ||
                //skip expired licenses
                ($expiryDate && strtotime($expiryDate) < time())
            ) {
                continue;
            }


            $sql = "INSERT INTO ".$this->db->table_name('licenses')." 
                    (
                    `product_id`,
                    `product_option_value_id`,
                    `status`,
                    `order_id`,
                    `order_product_id`,
                    `license_key`,
                    `url`,
                    `settings`,
                    `expiry_date`,
                    `date_added`)
                    VALUES 
                    (
                    '" . $product_id . "',
                    '" . $product_option_value_id . "',
                    1,
                    0,
                    0,
                    '" . $this->db->escape($eCode) . "',
                    '" . $this->db->escape($eUrl) . "',
                    '" . $this->db->escape($settings) . "',
                    " . ($expiryDate ? "'" . $this->db->escape($expiryDate) . "'" : "NULL") . ",                    
                    NOW()
                    )";
            //use safe adding
            $result = $this->db->query($sql, true);
            $count += ($result ? 1 : 0);

            $license_id = (int)$this->db->getLastId();
            //do assign to waiting orders
            $sql = "SELECT * 
                    FROM ".$this->db->table_name('order_options')." oo
                    INNER JOIN ".$this->db->table_name('orders')." o
                        ON ( o.order_id = oo.order_id )
                    INNER JOIN ".$this->db->table_name('order_products')." op
                        ON (op.order_product_id = oo.order_product_id
                            AND op.product_id = '".$product_id."' )
                    WHERE o.order_status_id= ".$this->order_status->getStatusByTextId('processing')." 
                        AND oo.product_option_value_id = '".$product_option_value_id."'
                    LIMIT 0,1";

            $result = $this->db->query($sql);
            if ($result->row) {
                $row = $result->row;
                $order_id = (int)$row['order_id'];
                $sql = "UPDATE ".$this->db->table_name('licenses')."
                        SET order_id = '".$order_id."',
                            order_product_id = '".(int)$row['order_product_id']."'
                        WHERE license_id = '".$license_id."'";
                $this->db->query($sql);

                $this->db->beginTransaction();
                try {
                    $data = [
                        'order_id'        => $order_id,
                        'order_status_id' => $this->order_status->getStatusByTextId('completed'),
                        'notify'          => 1,
                    ];
                    $oHistory = new OrderHistory($data);
                    $oHistory->save();
                    $this->db->commit();
                    Registry::cache()->flush('order');
                    H::event('admin\SendOrderStatusNotifyEmail', [new ABaseEvent($data)]);
                } catch (Exception $e) {

                    $this->log->error(__CLASS__ . ': ' . sprintf(
                            $this->language->get('error_system'),
                            $this->html->getSecureURL('tool/error_log')
                        ) . "\n" . $e->getMessage()
                    );
                    $this->db->rollback();
                }
            }

        }
        return $count;
    }

    /**
     * @param $product_id
     * @param $option_ids
     * @param int $language_id
     *
     * @return array|null
     * @throws Exception
     */
    public function getOptionValuesNames($product_id, $option_ids, $language_id = 0)
    {
        if (empty($product_id) || !$option_ids) {
            return null;
        }
        if (!$language_id) {
            $language_id = $this->language->getContentLanguageID();
        }
        $ids = [];
        foreach ($option_ids as $id) {
            $id = (int)$id;
            if ($id) {
                $ids[] = $id;
            }
        }
        if (!$ids) {
            return [];
        }

        return (array)ProductOptionValueDescription::whereIn('product_option_value_id', $ids)
            ->where('product_id', '=', $product_id)
            ->where('language_id', '=', $language_id)
            ->get()?->pluck('name', 'product_option_value_id')->toArray();
    }
}