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

namespace Tests\unit\models\locale;

use abc\models\locale\Language;
use Illuminate\Validation\ValidationException;
use Tests\unit\ATestCase;

/**
 * Class LanguageModelTest
 */
class LanguageModelTest extends ATestCase
{


    public function testValidator()
    {

        $language = new Language(
            [
                'language_id' => 0,
                'name' => 'd',
                'code' => 6,
                'locale' => 6,
                'image' => 1,
                'directory' => '',
                'filename' => 'sdhjfdzgfsdfgghfgfdggdfgdfghfdtgdsfgfdghfghbdfhfghdfgdfgdfgdfgdfgdfgdfgdfgdfgdfgdfgdfg',
                'sort_order' => 'fdjfgfh',
                'status' => 'dfsgdfgdfgdfgdfg'
            ]
        );
        $errors = [];
        try {
            $language->validate();
        } catch (ValidationException $e) {
            $errors = $language->errors()['validation'];
        }
        $this->assertCount(9, $errors);

        $language = new Language(
            [
                'language_id' => 2,
                'name' => 'somestring',
                'code' => 'g',
                'locale' => 'somestring',
                'image' => 'somestring',
                'directory' => 'somestring',
                'filename' => 'somestring',
                'sort_order' => 1,
                'status' => 1
            ]
        );
        $errors = [];
        try {
            $language->validate();
        } catch (ValidationException $e) {
            $errors = $language->errors()['validation'];
        }
        $this->assertCount(0, $errors);

    }
}