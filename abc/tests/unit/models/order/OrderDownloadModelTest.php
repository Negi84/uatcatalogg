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

namespace Tests\unit\models\order;

use abc\models\order\OrderDownload;
use Illuminate\Validation\ValidationException;
use Tests\unit\ATestCase;

/**
 * Class OrderDownloadModelTest
 */
class OrderDownloadModelTest extends ATestCase
{

    protected function setUp():void
    {
        //init
    }

    public function testValidator()
    {
        //validate
        $data = [
            'order_id'                 => 'fail',
            'order_product_id'         => 'fail',
            'name'                     => -0.000000000123232,
            'filename'                 => -0.000000000123232,
            'mask'                     => -0.000000000123232,
            'download_id'              => 'fail',
            'status'                   => 'fail',
            'remaining_count'          => 'fail',
            'percentage'               => 'fail',
            'expire_date'              => 'fail',
            'sort_order'               => 'fail',
            'activate'                 => -0.000000000123232,
            'activate_order_status_id' => 'fail',
            'attributes_data'          => 'fail',
        ];

        $orderDownload = new OrderDownload();
        $errors = [];
        try {
            $orderDownload->validate($data);
        } catch (ValidationException $e) {
            $errors = $orderDownload->errors()['validation'];
        }
        $this->assertCount(13, $errors);

        //check validation of presence in database
        $data = [
            'order_id'                 => 10000000,
            'order_product_id'         => 10000000,
            'download_id'              => 1000000,
            'activate_order_status_id' => 1000000000,
            // fill required junk
            'activate'                 => 'test',
            'mask'                     => 'test',
            'filename'                 => 'test',
            'name'                     => 'test',
        ];

        $orderDownload = new OrderDownload();
        $errors = [];
        try {
            $orderDownload->validate($data);
        } catch (ValidationException $e) {
            $errors = $orderDownload->errors()['validation'];
        }
        $this->assertCount(4, $errors);

        //check validation of nullables
        $data = [
            'download_id'              => null,
            'remaining_count'          => null,
            'percentage'               => null,
            'expire_date'              => null,
            // fill required junk
            'order_id'                 => 2,
            'order_product_id'         => 6,
            'activate_order_status_id' => 1,
            'activate'                 => 'test',
            'mask'                     => 'test',
            'filename'                 => 'test',
            'name'                     => 'test',
        ];

        $orderDownload = new OrderDownload();
        $errors = [];
        try {
            $orderDownload->validate($data);
        } catch (ValidationException $e) {
            $errors = $orderDownload->errors()['validation'];
        }
        $this->assertCount(0, $errors);

        //valid data
        $data = [
            'order_id'                 => 2,
            'order_product_id'         => 6,
            'name'                     => 'test-download',
            'filename'                 => 'http://',
            'mask'                     => 'test-mask',
            'download_id'              => 1,
            'status'                   => 0,
            'remaining_count'          => 458,
            'percentage'               => 0,
            'expire_date'              => '2019-05-01 00:00:00',
            'sort_order'               => 1,
            'activate'                 => 'sssssss',
            'activate_order_status_id' => 1,
            'attributes_data'          => ['somedata' => 'somevalue'],
        ];

        $orderDownload = new OrderDownload($data);
        $errors = [];
        try {
            $orderDownload->validate($data);
            $orderDownload->save();
        } catch (ValidationException $e) {
            $errors = $orderDownload->errors()['validation'];
        }
        $this->assertCount(0, $errors);
        $orderDownload->forceDelete();
    }

}