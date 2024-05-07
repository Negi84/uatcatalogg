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

namespace Tests\unit\core\lib;

use abc\core\engine\Registry;
use stdClass;
use Tests\unit\ATestCase;

/**
 * Class CacheTest
 */
class CacheTest extends ATestCase
{

    public function testCache1()
    {
        $key = 'TestCacheKey';
        $data = new StdClass;
        $data->property = 'unit';
        $cache = Registry::cache();
        $cache->flush();
        $cache->put($key, $data);
        $this->assertEquals('unit', $cache->get($key)->property);
        $cache->flush('unit');
    }

    public function testNonTaggableCache()
    {
        $key = 'TestCacheKey';
        $data = new StdClass;
        $data->property = 'unit';
        $tags = 'tag-unit';

        $cache = Registry::cache();
        $cache->flush();
        $cache->tags($tags)->put($key, $data);
        $this->assertEquals('unit', $cache->get($key)->property);
        $cache->flush($tags);

        $this->assertNull($cache->get($key));
    }

//    public function testMemCachedTags()
//    {
//        if( isset(ABC::env('CACHE')['memcached'])) {
//            $key = 'TestCacheKey';
//            $data = ['property' => 'unit'];
//            $tags = 'tagunit';
//
//            $cache = Registry::cache();
//            $cache->setCurrentStore('memcached');
//            $cache->flush();
//
//            $cache->tags($tags)->put($key, $data);
//
//            $this->assertEquals($data, $cache->tags($tags)->get($key));
//
//            $cache->put('other-cache', 'test');
//            $cache->flush($tags);
//
//            $this->assertNull($cache->get($key));
//            $this->assertEquals('test', $cache->get('other-cache'));
//
//            //check abc taggable cache based on prefix
//            $cache->flush();
//            $key = 'product.'.$key;
//            $cache->put($key, $data);
//            $this->assertEquals($data, $cache->tags('product')->get($key));
//            $this->assertEquals($data, $cache->get($key));
//            $cache->flush();
//        }else{
//            $this->assertEquals('1','1');
//        }
//    }
}