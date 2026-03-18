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
 * Class HiPayPaymentsCustomerCard
 */
class HiPayPaymentsCustomerCard extends ObjectModel
{
    /** @var int */
    public $id_hipaypayments_customer_card;

    /** @var int */
    public $id_customer;

    /** @var string */
    public $payment_product;

    /** @var string */
    public $card_token;

    /** @var string */
    public $card_brand;

    /** @var string */
    public $card_pan;

    /** @var string */
    public $card_expiry_month;

    /** @var string */
    public $card_expiry_year;

    /** @var string */
    public $card_holder;

    /** @var string */
    public $date_add;

    /** @var mixed[] */
    public static $definition = [
        'table' => 'hipaypayments_customer_card',
        'primary' => 'id_hipaypayments_customer_card',
        'fields' => [
            'id_customer' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'payment_product' => ['type' => self::TYPE_STRING, 'allow_null' => false, 'required' => true],
            'card_token' => ['type' => self::TYPE_STRING, 'allow_null' => false, 'required' => true],
            'card_brand' => ['type' => self::TYPE_STRING, 'allow_null' => false, 'required' => true],
            'card_pan' => ['type' => self::TYPE_STRING, 'allow_null' => false, 'required' => true],
            'card_expiry_month' => ['type' => self::TYPE_STRING, 'allow_null' => false, 'required' => true],
            'card_expiry_year' => ['type' => self::TYPE_STRING, 'allow_null' => false, 'required' => true],
            'card_holder' => ['type' => self::TYPE_STRING, 'allow_null' => false, 'required' => true],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];

    /**
     * @param int $customerId
     * @return mixed[]
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public static function getCardsByCustomerId(int $customerId): array
    {
        $dbQuery = (new DbQuery())
            ->select(self::$definition['primary'])
            ->from(self::$definition['table'])
            ->where(pSQL(sprintf('id_customer = %d', (int) $customerId)));

        $rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($dbQuery);
        $results = [];
        foreach ($rows as $row) {
            $results[] = new self((int) $row[self::$definition['primary']]);
        }

        return $results;
    }

    /**
     * @param int $customerId
     * @param string $token
     * @return bool
     */
    public static function checkCustomerToken(int $customerId, string $token): bool
    {
        $dbQuery = (new DbQuery())
            ->select(self::$definition['primary'])
            ->from(self::$definition['table'])
            ->where(sprintf('id_customer = %d AND card_token = "%s"', (int) $customerId, pSQL($token)));

        return (bool) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($dbQuery);
    }

    /**
     * @param string $pan
     * @param int    $customerId
     * @return int
     */
    public static function getCustomerCardIdByPan(string $pan, int $customerId): int
    {
        $dbQuery = (new DbQuery())
            ->select(self::$definition['primary'])
            ->from(self::$definition['table'])
            ->where(sprintf('id_customer = %d AND card_pan = "%s"', (int) $customerId, pSQL($pan)))
            ->orderBy(sprintf('%s DESC', pSQL(self::$definition['primary'])));

        return (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($dbQuery);
    }
}
