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

use abc\models\catalog\Manufacturer;
use Exception;
use PDOException;
use PHPUnit\Framework\Warning;
use Tests\unit\ATestCase;

class ManufacturerModelTest extends ATestCase
{
    /**
     * @return bool|mixed
     */
    public function testCreateManufacturer()
    {
        $arManufacturer = [
            'name' => 'Manufacturer create test',
            'sort_order'=> 100,
            'manufacturer_store' => [0],
            'keyword' => 'test-create-manufacturer'
        ];
        try {
        $manufacturerId = Manufacturer::addManufacturer($arManufacturer);
        } catch (PDOException|Warning|Exception $e) {
            $this->fail($e->getMessage());
        }

        $this->assertIsInt($manufacturerId);
        return $manufacturerId;
    }

    /**
     * @depends testCreateManufacturer
     *
     * @param int $manufacturerId
     */
    public function testReadManufacturer(int $manufacturerId)
    {
        $manufacturer = Manufacturer::find($manufacturerId);
        $this->assertEquals('Manufacturer create test', $manufacturer->name);
    }


    /**
     * @depends testCreateManufacturer
     *
     * @param int $manufacturerId
     *
     */
    public function testUpdateManufacturer(int $manufacturerId)
    {
        $arManufacturer = [
            'name' => 'Manufacturer update test',
            'sort_order'=> 300,
            ];

        (new Manufacturer())->editManufacturer($manufacturerId, $arManufacturer);

        $manufacturer = Manufacturer::find($manufacturerId);
        $this->assertEquals(300, $manufacturer->sort_order);
    }

    /**
     * @depends testCreateManufacturer
     *
     * @param int $manufacturerId
     *
     */
    public function testDeleteManufacturer(int $manufacturerId)
    {
        try {
            Manufacturer::deleteManufacturer($manufacturerId);
        } catch (PDOException|Warning|Exception $e) {
            $this->fail($e->getMessage());
        }

        $this->assertNull(Manufacturer::find($manufacturerId));
    }
}
