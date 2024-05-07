<?php

namespace abc\extensions\licensing\models\storefront\extension;

use abc\core\engine\Model;

/**
 * Class ModelExtensionLicensing
 *
 * @package abc\models\admin
 */
class ModelExtensionLicensing extends Model
{

    public $data = [];
    public $error = [];

    /**
     * @param $product_id
     *
     * @return array
     * @throws \Exception
     */
    public function getLicenses($product_id)
    {
        $product_id = (int)$product_id;
        if (!$product_id) {
            return array();
        }

        $sql = "SELECT l.*, pov.* 
                FROM ".$this->db->table_name('licenses')." l
                LEFT JOIN ".$this->db->table_name('product_option_values')." pov
                    ON pov.product_option_value_id = l.product_option_value_id 
                WHERE l.product_id = ".$product_id."
                    AND ( expiry_date IS NULL OR expiry_date > NOW() )  
                ORDER BY l.date_added ASC";
        $result = $this->db->query($sql);
        return $result->rows;
    }

    /**
     * @param $order_id
     *
     * @return mixed
     * @throws \Exception
     */
    public function getOrderLicensedProducts($order_id)
    {
        $order_id = (int)$order_id;
        $sql = "SELECT 
                    op.product_id,
                    op.name as product_name,
                    op.quantity, 
                    COALESCE(op.order_product_id,0) as order_product_id,
                    COALESCE(oo.product_option_value_id,0) as product_option_value_id,
                    COALESCE(l.license_id,0) as license_id,
                    COALESCE(l.license_key,'') as license_key
                FROM ".$this->db->table_name('order_products')." op
                INNER JOIN ".$this->db->table_name('order_options')." oo
                    ON (oo.order_product_id = op.order_product_id)
                INNER JOIN ".$this->db->table_name('products')." p
                    ON (p.product_id = op.product_id AND p.license = 1)
                LEFT JOIN ".$this->db->table_name('licenses')." l
                    ON ( l.order_id = ".$order_id." AND op.order_product_id = l.order_product_id
                        AND oo.product_option_value_id = l.product_option_value_id )
                WHERE op.order_id = ".$order_id."
                ORDER BY op.product_id ASC, order_product_id ASC";
        $result = $this->db->query($sql);
        return $result->rows;
    }

    /**
     * @param $order_product_id
     *
     * @return mixed
     * @throws \Exception
     */
    public function getValidityByOrderProductID($order_product_id)
    {
        $order_product_id = (int)$order_product_id;
        $sql = "SELECT * 
                FROM ".$this->db->table_name('order_options')."
                WHERE order_product_id= ".$order_product_id." AND name='License Validity Period'";
        $result = $this->db->query($sql);
        return $result->row['value'];
    }

    /**
     * @param array $product_ids
     *
     * @return array
     * @throws \Exception
     */
    public function getProductsLicenses($product_ids)
    {
        if (!$product_ids) {
            return array();
        }
        $product_ids = (array)$product_ids;
        $output = array();
        foreach ($product_ids as $order_product_id => $order_product) {
            if (!$order_product || !$order_product_id) {
                continue;
            }
            //when few products with same id but different options
            foreach ($order_product['product_option_values'] as $opt_val_id) {
                $sql = "SELECT *
                        FROM ".$this->db->table_name('licenses')."
                        WHERE status = 1 AND order_id = 0 AND order_product_id = 0 
                            AND product_id = '".(int)$order_product['product_id']."'
                            AND product_option_value_id = '".(int)$opt_val_id."'
                        ORDER BY product_id ASC, date_modified DESC";
                $result = $this->db->query($sql);
                foreach ($result->rows as $row) {
                    $output[$row['product_option_value_id']][] = $row;
                }
            }
        }
        return $output;
    }

    public function useLicense($license_id, $order_id, $order_product_id)
    {
        $license_id = (int)$license_id;
        $order_id = (int)$order_id;
        $order_product_id = (int)$order_product_id;
        if (!$license_id || !$order_product_id || !$order_id) {
            return false;
        }

        $sql = "UPDATE ".$this->db->table_name('licenses')."
                SET 
                    order_id = ".$order_id.",
                    order_product_id = ".$order_product_id."
                WHERE license_id = ".$license_id;
        //do safe update
        $result = $this->db->query($sql, true);

        return $result;
    }
}
