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

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class AbstractAdvancedPaymentMethod
 */
abstract class AbstractAdvancedPaymentMethod extends PaymentMethod
{
    const APM_DISPLAY_REDIRECT = 'redirect';
    const APM_DISPLAY_HOSTED_FIELDS = 'hosted_fields';
    const APM_DISPLAY_BINARY = 'binary';

    /** @var bool */
    public $minAmountForced;

    /** @var bool */
    public $maxAmountForced;

    /** @var bool */
    public $countriesForced;

    /** @var bool */
    public $currenciesForced;

    /** @var bool */
    public $currenciesCountriesManaged = true;

    /** @var string */
    public $displayMode;

    /** @var int */
    public $position;
}
