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

namespace HiPay\PrestaShop\Settings\Entity;

use AG\PSModuleUtils\Tools;
use HiPay\PrestaShop\Utils\AmountOfMoney;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class PaymentMethod
 */
class PaymentMethod
{
    /** @var string */
    public $code;

    /** @var string */
    public $name;

    /** @var bool */
    public $enabled;

    /** @var float */
    public $minAmount;

    /** @var float */
    public $maxAmount;

    /** @var bool */
    public $currenciesCountriesManaged;

    /** @var string[] */
    public $currencies;

    /** @var string[] */
    public $countries;

    /** @var bool */
    public $canRefund;

    /**
     * @param \Cart $cart
     * @return bool
     * @throws \Exception
     */
    public function isEligibleWithCart(\Cart $cart): bool
    {
        if (false === $this->enabled) {
            return false;
        }
        $cartCurrencyIsoCode = Tools::getIsoCurrencyCodeById($cart->id_currency);
        $orderTotal = AmountOfMoney::fromStandardUnit($cart->getOrderTotal(), $cartCurrencyIsoCode);
        $minAmount = AmountOfMoney::fromStandardUnit($this->minAmount, $cartCurrencyIsoCode);
        $maxAmount = AmountOfMoney::fromStandardUnit($this->maxAmount, $cartCurrencyIsoCode);
        if (
            ($minAmount->getAmount() && $orderTotal->compare($minAmount) < 0) ||
            ($maxAmount->getAmount() && $orderTotal->compare($maxAmount) > 0)
        ) {
            return false;
        }
        if (true === $this->currenciesCountriesManaged) {
            if ($this->currencies && !in_array($cartCurrencyIsoCode, $this->currencies)) {
                return false;
            }
            $customerCountryIsoCode = \Country::getIsoById((new \Address((int) $cart->id_address_delivery))->id_country);
            if ($this->countries && !in_array($customerCountryIsoCode, $this->countries)) {
                return false;
            }
        }

        return true;
    }
}
