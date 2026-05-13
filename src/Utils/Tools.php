<?php
/**
 * 2025 HiPay
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0).
 * It is also available through the world-wide-web at this URL: https://opensource.org/licenses/AFL-3.0
 *
 * @author    HiPay partner
 * @copyright 2025
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

namespace HiPay\PrestaShop\Utils;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Tools
 */
class Tools
{
    /**
     * @param int      $idModule
     * @param int      $idLang
     * @param int|null $idShop
     * @return mixed[]
     * @throws \PrestaShopDatabaseException
     */
    public static function getPaymentCurrencies(int $idModule, int $idLang, int $idShop = null): array
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
     * @param int      $idModule
     * @param int      $idLang
     * @param int|null $idShop
     * @return mixed[]
     * @throws \PrestaShopDatabaseException
     */
    public static function getPaymentCountries(int $idModule, int $idLang, int $idShop = null): array
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
        $filtered = array_filter($enabledCountries, function ($item) use ($idList) {
            return in_array($item['id_country'], $idList);
        });

        return $filtered ?: [];
    }

    /**
     * @param int $cartId
     * @return \Order
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
