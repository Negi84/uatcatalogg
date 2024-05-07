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

namespace abc\models\casts;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Database\Eloquent\Model;

class NullableInt implements CastsAttributes
{
    use HasAttributes;
    /**
     * Cast the given value.
     *
     * @param  Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return int|null
     */
    public function get($model, $key, $value, $attributes)
    {
        return (int)$value ?: null;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  Model  $model
     * @param  string $key
     * @param  array  $value
     * @param  array  $attributes
     * @return int|null
     */
    public function set($model, $key, $value, $attributes)
    {
        return (int)$value ?: null;
    }
}