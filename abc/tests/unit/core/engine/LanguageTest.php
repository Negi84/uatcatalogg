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

namespace Tests\unit\core\engine;

use abc\core\engine\ALanguage;
use abc\core\engine\Registry;
use Tests\unit\ATestCase;

/**
 * Class LanguageTest
 */
class LanguageTest extends ATestCase{

    protected function setUp(): void
    {
        Registry::config()->set('config_cache', 0);
    }

    public function testLoadFromDb()
    {
        //let use sf language
        $language = new ALanguage(Registry::getInstance(), 'en');
        $language->load('english');
        $this->assertEquals('en', $language->get('code'));

        $row = Registry::db()->table('language_definitions')->where(
            [
                'section'      => 1,
                'block'        => 'english',
                'language_key' => 'code'
            ]
        )->first();
        $this->assertEquals('en', $row->language_value);
    }
}