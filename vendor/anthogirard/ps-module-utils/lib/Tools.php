<?php
/*
 * MIT License
 *
 * Copyright (c) 2022 Anthony Girard
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 */

namespace AG\PSModuleUtils;

use Symfony\Component\Filesystem\Filesystem;
use RandomLib\Factory as RandomLib;
use SecurityLib\Strength;

/**
 * Class Tools
 * @package AG\PSModuleUtils
 */
class Tools
{
    const RANDOM_STRING_CHARS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     * @param string $value
     * @return string
     */
    public static function hash($value)
    {
        return md5(_COOKIE_IV_.$value);
    }

    /**
     * @param string $source
     * @param string $destination
     * @return void
     */
    public static function copy($source, $destination)
    {
        $filesystem = new Filesystem();
        $filesystem->copy($source, $destination, true);
    }

    /**
     * @return mixed[]
     */
    public static function getServerHttpHeaders()
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) !== 'HTTP_') {
                continue;
            }
            $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
            $headers[$header] = $value;
        }

        return $headers;
    }

    /**
     * Use this method to insure compatibility with earlier versions of PrestaShop
     * @param int $idCurrency
     * @return string
     */
    public static function getIsoCurrencyCodeById($idCurrency)
    {
        static $cache;

        if (isset($cache[$idCurrency])) {
            return $cache[$idCurrency];
        }
        $currency = new \Currency((int) $idCurrency);
        if (!\Validate::isLoadedObject($currency)) {
            return '';
        }
        $cache[$idCurrency] = $currency->iso_code;

        return $currency->iso_code;
    }

    /**
     * @param int $length
     * @return string
     */
    public static function generateRandomString($length = 7)
    {
        $factory = new RandomLib();
        $generator = $factory->getGenerator(new Strength(Strength::LOW));

        return $generator->generateString($length, self::RANDOM_STRING_CHARS);
    }
}
