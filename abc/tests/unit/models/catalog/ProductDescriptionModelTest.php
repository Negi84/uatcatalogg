<?php
/**
 * AbanteCart, Ideal Open Source Ecommerce Solution
 * https://www.abantecart.com
 *
 * Copyright (c) 2011-2023  Belavier Commerce LLC
 *
 * This source file is subject to Open Software License (OSL 3.0)
 * License details is bundled with this package in the file LICENSE.txt.
 * It is also available at this URL:
 * <https://www.opensource.org/licenses/OSL-3.0>
 *
 * UPGRADE NOTE:
 * Do not edit or add to this file if you wish to upgrade AbanteCart to newer
 * versions in the future. If you wish to customize AbanteCart for your
 * needs please refer to https://www.abantecart.com for more information.
 */

namespace Tests\unit\models\catalog;

use abc\models\catalog\ProductDescription;
use Illuminate\Validation\ValidationException;
use Tests\unit\ATestCase;

/**
 * Class ProductDescriptionModelTest
 */
class ProductDescriptionModelTest extends ATestCase{

    public function testValidator()
    {
        $productDescription = new ProductDescription();
        $errors = [];
        try {
            $data = [
                'product_id'       => false,
                'language_id'      => false,
                'name'             => 'e',
                'meta_keywords'    => false,
                'meta_description' => false,
                'description'      => false,
                'blurb'            => false,
            ];
            $productDescription->validate( $data );
        } catch (ValidationException $e) {
            $errors = $productDescription->errors()['validation'];
        }

        $this->assertCount(7, $errors);


        $errors = [];
        try {
            $data = [
                'product_id'       => 50,
                'language_id'      => 1,
                'name'             => 'test',
                'meta_keywords'    => 'test',
                'meta_description' => 'test',
                'description'      => 'test',
                'blurb'            => 'test',
            ];
            $productDescription->validate($data);
        } catch (ValidationException $e) {
            $errors = $productDescription->errors()['validation'];
        }
        $this->assertCount(0, $errors);

        $errors = [];
        try {
            $data = [
                'product_id'       => 50,
                'language_id'      => 1,
                'name'             => 'test',
                'meta_keywords'    => '',
                'meta_description' => '',
                'description'      => '',
                'blurb'            => '',
            ];
            $productDescription->validate($data);
        } catch (ValidationException $e) {
            $errors = $productDescription->errors()['validation'];
        }
        $this->assertCount(0, $errors);
        $errors = [];
        try {
            $data = [
                'product_id'       => 50,
                'language_id'      => 1,
                'name'             => 'test',
            ];
            $productDescription->validate($data);
        } catch (ValidationException $e) {
            $errors = $productDescription->errors()['validation'];
        }
        $this->assertCount(0, $errors);

    }
}