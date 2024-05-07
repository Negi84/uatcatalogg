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

use abc\core\engine\Registry;
use abc\models\catalog\ProductOption;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Psr\SimpleCache\InvalidArgumentException;

trait ProductOptionsTrait
{

    /**
     * Check if any of input options are required and provided
     *
     * @param int $product_id
     * @param array $inData
     *
     * @return array
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function validateProductOptions(int $product_id, array $inData)
    {
        $errors = [];
        if (!$product_id && !$inData ) {
            return [];
        }
        /** @var Collection|ProductOption $productOptions */
        $productOptions = ProductOption::with('description')
            ->active()
            ->where('product_id',$product_id)
            ->get();

        if ($productOptions) {
            $language = Registry::language();
            $language->load('checkout/cart');
            foreach ($productOptions as $option) {
                if ($option->required) {
                    if (!$inData[$option->product_option_id]) {
                        $errors[] = $option->description->name.': '.$language->get('error_required_options');
                    }
                }

                if ($option->regexp_pattern
                    && !preg_match($option->regexp_pattern, (string)$inData[$option->product_option_id])
                ) {
                    $errors[] = $option->description->name.': '.$option->description->error_text;
                }
            }
        }
        return $errors;
    }
}