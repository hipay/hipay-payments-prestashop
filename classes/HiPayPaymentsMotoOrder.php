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

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class HiPayPaymentsMotoOrder
 */
class HiPayPaymentsMotoOrder extends ObjectModel
{
    /** @var int */
    public $id_order;

    /** @var int */
    public $id_cart;

    /** @var string */
    public $date_add;

    /** @var mixed[] */
    public static $definition = [
        'table' => 'hipaypayments_moto_order',
        'primary' => 'id_hipaypayments_moto_order',
        'fields' => [
            'id_order' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'id_cart' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];

    /**
     * @param int $orderId
     * @return HiPayPaymentsMotoOrder
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public static function getHiPayMotoOrderByPsOrderId(int $orderId): HiPayPaymentsMotoOrder
    {
        $dbQuery = (new DbQuery())
            ->select(self::$definition['primary'])
            ->from(self::$definition['table'])
            ->where(pSQL(sprintf('id_order = %d', (int) $orderId)));

        $id = (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($dbQuery);

        return new self((int) $id);
    }
}
