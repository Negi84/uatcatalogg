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

namespace Tests\unit\models\customer;

use abc\models\customer\Address;
use Illuminate\Validation\ValidationException;
use Tests\unit\ATestCase;

/**
 * Class AddressModelTest
 */
class AddressModelTest extends ATestCase{

    public function testUpdate()
    {
        $address = Address::find(1);
        $address->update(['company' => 'abc']);
        $this->assertEquals('abc', $address->company);
        $address->update(['company' => '']);
    }

    public function testValidation()
    {
        $errors = [];
        $address = new Address(
            [
                'company' => '',
                'firstname' => '',
                'lastname' => '',
                'address_1' => '',
                'address_2' => '',
                'postcode' => '',
                'city' => '',
                'country_id' => '',
                'zone_id' => ''
            ]
        );
        try {
            $address->validate();
        }catch(ValidationException $e){
            $errors = $address->errors()['validation'];
        }

        $this->assertEquals(
            [
             'firstname',
             'lastname',
             'address_1',
             'postcode',
             'city',
             'country_id',
             'zone_id',
            ],
            array_keys($errors));

        $errors = [];

        $address = Address::find(1);

        //success
        try {
            $address->validate(
                [   'customer_id' => 1,
                    'company' => 'ACORN-Team',
                    'firstname' => 'test-first-name',
                    'lastname' => 'test-last-name',
                    'address_1' => 'Grushevsky Street',
                    'address_2' => 'Motel Crocodile',
                    'postcode' => '36021',
                    'city' => 'Poltava',
                    'country_id' => '12',
                    'zone_id' => '23'
                ]
            );
        }catch(ValidationException $e){
            $errors = $address->errors()['validation'];
        }
        $this->assertCount(0, $errors);

        //check nullable zone_id
        $address = new Address();
        $address->fill( ['zone_id' => '0'] );
        $this->assertEquals( null, $address->zone_id);

        $errors = [];
        //try save without customer_id and address_id - expect errors
        try {
            $address = new Address();
            $address->validate(
                [   'company' => 'abc',
                    'firstname' => '',
                    'lastname' => '',
                    'address_1' => '',
                    'address_2' => 'dsdsds',
                    'postcode' => '',
                    'city' => '',
                    'country_id' => '',
                    'zone_id' => ''
                ]
            );
        }catch(ValidationException $e){
            $errors = $address->errors()['validation'];
        }
        $this->assertCount(7, $errors);

        $errors = [];
        //try save without customer_id - expect errors
        try {
            $address->validate(
                [
                    'address_id' => '1',
                    'customer_id' => '',
                    'firstname' => 'test',
                    'lastname' => 'test',
                    'address_1' => 'test',
                    'address_2' => 'dsdsds',
                    'postcode' => '1234',
                    'city' => 'Poltava',
                    'country_id' => '12',
                    'zone_id' => '12'
                ]
            );
        }catch(ValidationException $e){
            $errors = $address->errors()['validation'];
        }
        $this->assertCount(1, $errors);


        $errors = [];
        //try save without customer_id - expect errors
        try {
            $address->validate(
                [
                    'address_id' => '1',
                    'customer_id' => '',
                    'firstname' => 'test',
                    'lastname' => 'test',
                    'address_1' => 'test',
                    'address_2' => 'dsdsds',
                    'postcode' => '1234',
                    'city' => 'Poltava',
                    'country_id' => '12',
                    'zone_id' => '0'
                ]
            );
        }catch(ValidationException $e){
            $errors = $address->errors()['validation'];
        }
        $this->assertCount(1, $errors);
    }
}