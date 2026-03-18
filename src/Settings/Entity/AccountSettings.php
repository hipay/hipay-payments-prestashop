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

use HiPay\PrestaShop\Api\Credentials;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class AccountSettings
 */
class AccountSettings
{
    const MODE_PRODUCTION = 'prod';
    const MODE_TEST = 'test';

    /** @var bool */
    public $useDemoMode;

    /** @var string */
    public $environment;

    /** @var PrivateIdentifiers */
    public $testPrivateIdentifiers;

    /** @var PublicIdentifiers */
    public $testPublicIdentifiers;

    /** @var PrivateIdentifiers */
    public $prodPrivateIdentifiers;

    /** @var PublicIdentifiers */
    public $prodPublicIdentifiers;

    /** @var PrivateIdentifiers */
    public $applePayTestPrivateIdentifiers;

    /** @var PublicIdentifiers */
    public $applePayTestPublicIdentifiers;

    /** @var PrivateIdentifiers */
    public $applePayProdPrivateIdentifiers;

    /** @var PublicIdentifiers */
    public $applePayProdPublicIdentifiers;

    /** @var PrivateIdentifiers */
    public $motoTestPrivateIdentifiers;

    /** @var PrivateIdentifiers */
    public $motoProdPrivateIdentifiers;

    /** @var string[] */
    public $hashingAlgorithms;

    /**
     * @param string $accessibility
     * @return bool
     */
    public function isApplePayCredentialsConfigured(string $accessibility): bool
    {
        if (true === $this->useDemoMode) {
            return false;
        }

        $isPrivate = $accessibility === Credentials::CREDENTIALS_ACCESSIBILITY_PRIVATE;
        $isTest = $this->environment === AccountSettings::MODE_TEST;

        $identifiers = $isPrivate
            ? ($isTest ? $this->applePayTestPrivateIdentifiers : $this->applePayProdPrivateIdentifiers)
            : ($isTest ? $this->applePayTestPublicIdentifiers : $this->applePayProdPublicIdentifiers);

        return (bool) $identifiers->username;
    }

    /**
     * @return bool
     */
    public function motoCredentialsIsConfigured(): bool
    {
        if (true === $this->useDemoMode) {
            return false;
        }

        $isTest = $this->environment === AccountSettings::MODE_TEST;

        $identifiers = $isTest ? $this->motoTestPrivateIdentifiers : $this->motoProdPrivateIdentifiers;

        return (bool) $identifiers->username;
    }
}
