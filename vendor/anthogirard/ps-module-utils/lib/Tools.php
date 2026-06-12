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

class Tools
{
    public const RANDOM_STRING_CHARS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    public static function hash(string $value): string
    {
        return md5(_COOKIE_IV_.$value);
    }

    public static function copy(string $source, string $destination): void
    {
        $filesystem = new Filesystem();
        $filesystem->copy($source, $destination, true);
    }

    public static function getServerHttpHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (!str_starts_with($key, 'HTTP_')) {
                continue;
            }
            $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
            $headers[$header] = $value;
        }

        return $headers;
    }

    /**
     * Use this method to insure compatibility with earlier versions of PrestaShop
     */
    public static function getIsoCurrencyCodeById(int $idCurrency): string
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

    public static function generateRandomString(int $length = 7): string
    {
        if ($length < 0) {
            throw new \InvalidArgumentException('Length must be a non-negative integer');
        }

        $factory = new RandomLib();
        $generator = $factory->getGenerator(new Strength(Strength::LOW));

        return $generator->generateString($length, self::RANDOM_STRING_CHARS);
    }

    /**
     * @throws \PrestaShopDatabaseException
     */
    public static function getPaymentCurrencies(int $idModule, int $idLang, ?int $idShop = null): array
    {
        if (null === $idShop) {
            $idShop = \Context::getContext()->shop->id;
        }

        $dbQuery = (new \DbQuery())
            ->select('c.*, cl.*')
            ->from('module_currency', 'mc')
            ->leftJoin('currency', 'c', 'c.`id_currency` = mc.`id_currency`')
            ->leftJoin('currency_lang', 'cl', 'c.`id_currency` = cl.`id_currency`')
            ->where('c.`deleted` = 0')
            ->where(sprintf('mc.`id_module` = %d', (int) $idModule))
            ->where('c.`active` = 1')
            ->where(sprintf('mc.`id_shop` = %d', (int) $idShop))
            ->where(sprintf('cl.`id_lang` = %d', (int) $idLang))
            ->orderBy('c.`iso_code` ASC');

        $results = (array) \Db::getInstance()->executeS($dbQuery);

        return $results ?: [];
    }

    /**
     * @throws \PrestaShopDatabaseException
     */
    public static function getPaymentCountries(int $idModule, int $idLang, ?int $idShop = null): array
    {
        if (null === $idShop) {
            $idShop = \Context::getContext()->shop->id;
        }
        $enabledCountries = \Country::getCountries($idLang, true, false, false);
        if (!$enabledCountries) {
            return [];
        }

        $dbQuery = (new \DbQuery())
            ->select('mc.id_country')
            ->from('module_country', 'mc')
            ->where(sprintf('mc.`id_module` = %d', (int) $idModule))
            ->where(sprintf('mc.`id_shop` = %d', (int) $idShop));

        $results = \Db::getInstance()->executeS($dbQuery);
        if (!$results) {
            return [];
        }
        $idList = array_column((array) $results, 'id_country');
        $filtered = array_filter($enabledCountries, fn($item) => in_array($item['id_country'], $idList));

        return $filtered ?: [];
    }

    /**
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public static function getOrderByCartId(int $cartId): \Order
    {
        $dbQuery = (new \DbQuery())
            ->select('id_order')
            ->from('orders')
            ->where('id_cart = '.(int) $cartId);
        $idOrder = \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($dbQuery);

        return new \Order((int) $idOrder);
    }
}
