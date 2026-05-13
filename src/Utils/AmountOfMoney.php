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

use Money\Currency;
use Money\Money;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class AmountOfMoney
 */
class AmountOfMoney extends \AG\PSModuleUtils\Utils\AmountOfMoney
{
    /** @var float */
    protected $amount;

    /** @var int */
    protected $exp;

    /**
     * @param \AG\PSModuleUtils\Utils\AmountOfMoney $amount1
     * @param \AG\PSModuleUtils\Utils\AmountOfMoney $amount2
     * @param string                                $currencyCode
     * @return \AG\PSModuleUtils\Utils\AmountOfMoney
     * @throws \PrestaShop\Decimal\Exception\DivisionByZeroException
     */
    public static function subtract(\AG\PSModuleUtils\Utils\AmountOfMoney $amount1, \AG\PSModuleUtils\Utils\AmountOfMoney $amount2, string $currencyCode): \AG\PSModuleUtils\Utils\AmountOfMoney
    {
        $currency = new Currency($currencyCode);
        $total = new Money($amount1->getAmountInCents(), $currency);
        $subtract = new Money($amount2->getAmountInCents(), $currency);
        $result = $total->subtract($subtract);

        return self::fromSmallestUnit((float) $result->getAmount(), $currencyCode);
    }
}
