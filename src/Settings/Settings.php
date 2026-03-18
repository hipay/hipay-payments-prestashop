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

namespace HiPay\PrestaShop\Settings;

use HiPay\PrestaShop\Api\Credentials;
use HiPay\PrestaShop\Settings\Entity\AccountSettings;
use HiPay\PrestaShop\Settings\Entity\AdvancedPaymentSettings;
use HiPay\PrestaShop\Settings\Entity\CardPaymentSettings;
use HiPay\PrestaShop\Settings\Entity\MainSettings;
use HiPay\PrestaShop\Settings\Entity\ModuleInfo;
use HiPay\PrestaShop\Settings\Entity\PrivateCredentials;
use HiPay\PrestaShop\Settings\Entity\PrivateIdentifiers;
use HiPay\PrestaShop\Settings\Entity\PublicCredentials;
use HiPay\PrestaShop\Settings\Entity\PublicIdentifiers;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Settings
 */
class Settings extends \AG\PSModuleUtils\Settings\AbstractSettings
{
    const PS_CONFIG_KEY_ACCOUNT = 'HIPAY_SETTINGS_ACCOUNT';
    const PS_CONFIG_KEY_MAIN = 'HIPAY_SETTINGS_MAIN';
    const PS_CONFIG_KEY_CARD_PAYMENT = 'HIPAY_SETTINGS_CARD_PAYMENT';
    const PS_CONFIG_KEY_OTHER_PM = 'HIPAY_SETTINGS_OTHER_PM';
    const PS_CONFIG_KEY_MODULE_INFO = 'HIPAY_SETTINGS_MODULE_INFO';
    const PS_CONFIG_KEY_MODULE_UUID = 'HIPAY_PAYMENTS_UUID';
    const PS_CONFIG_KEY_MIGRATION_PAGE_DISPLAYED = 'HIPAYPAYMENTS_MIGRATION_PAGE_DISPLAYED';
    const DEFAULT_HASHING_ALGORITHM = 'SHA256';
    const HIPAY_DEMO_CREDENTIALS = [
        'public' => [
            'username' => '94698760.stage-secure-gateway.hipay-tpp.com',
            'password' => 'Test_ebrJlSbfFwuw8AFm9HvqqRha',
        ],
        'private' => [
            'username' => '94698759.stage-secure-gateway.hipay-tpp.com',
            'password' => 'Test_00f1093gTFCQbcdUHYQXzLWT',
            'passphrase' => 'H.M;DQD%7;bys2{J-R8KEa-oosvjV+w@zhnnVpa&',
        ],
        'hashingAlgorithm' => 'SHA256',
    ];

    /** @var AccountSettings */
    public $accountSettings;

    /** @var MainSettings */
    public $mainSettings;

    /** @var CardPaymentSettings */
    public $cardPaymentSettings;

    /** @var AdvancedPaymentSettings */
    public $otherPMSettings;

    /** @var ModuleInfo */
    public $moduleInfo;

    /** @var \StdClass */
    public $credentials;

    /** @var \StdClass */
    public $demoCredentials;

    /** @var int */
    public $pendingAuthStatusId;

    /** @var int */
    public $pendingCaptureStatusId;

    /** @var int */
    public $partiallyCapturedStatusId;

    /** @var int */
    public $partiallyRefundedStatusId;

    /** @var int */
    public $chargebackStatusId;

    /** @var bool */
    public $migrationPageDisplayed;

    /**
     * @return Settings
     */
    public function postLoading(): Settings
    {
        if (!$this->accountSettings->hashingAlgorithms) {
            $this->accountSettings->hashingAlgorithms = [
                Credentials::CREDENTIALS_TYPE_MAIN => self::DEFAULT_HASHING_ALGORITHM,
                Credentials::CREDENTIALS_TYPE_APPLE_PAY => self::DEFAULT_HASHING_ALGORITHM,
                Credentials::CREDENTIALS_TYPE_MOTO => self::DEFAULT_HASHING_ALGORITHM,
            ];
        }
        if (true === $this->accountSettings->useDemoMode) {
            $this->demoCredentials = new \StdClass();
            $this->demoCredentials->env = \HiPay\Fullservice\HTTP\Configuration\Configuration::API_ENV_STAGE;
            $this->demoCredentials->public = new PublicIdentifiers();
            $this->demoCredentials->public->username = self::HIPAY_DEMO_CREDENTIALS['public']['username'];
            $this->demoCredentials->public->password = self::HIPAY_DEMO_CREDENTIALS['public']['password'];
            $this->demoCredentials->private = new PrivateIdentifiers();
            $this->demoCredentials->private->username = self::HIPAY_DEMO_CREDENTIALS['private']['username'];
            $this->demoCredentials->private->password = self::HIPAY_DEMO_CREDENTIALS['private']['password'];
            $this->demoCredentials->private->secret = self::HIPAY_DEMO_CREDENTIALS['private']['passphrase'];
            $this->demoCredentials->hashingAlgorithm = self::HIPAY_DEMO_CREDENTIALS['hashingAlgorithm'];
        }

        if ($this->cardPaymentSettings->paymentMethods) {
            foreach ($this->cardPaymentSettings->paymentMethods as &$paymentMethod) {
                if (!$paymentMethod->currenciesCountriesManaged) {
                    $paymentMethod->currencies = [];
                    $paymentMethod->countries = [];
                }
            }
        }
        $this->pendingAuthStatusId = (int) \Configuration::getGlobalValue('HIPAYPAYMENTS_OS_PENDING_AUTH');
        $this->pendingCaptureStatusId = (int) \Configuration::getGlobalValue('HIPAYPAYMENTS_OS_PENDING_CAPTURE');
        $this->partiallyCapturedStatusId = (int) \Configuration::getGlobalValue('HIPAYPAYMENTS_OS_PARTIALLY_CAPTURED');
        $this->partiallyRefundedStatusId = (int) \Configuration::getGlobalValue('HIPAYPAYMENTS_OS_PARTIALLY_REFUNDED');
        $this->chargebackStatusId = (int) \Configuration::getGlobalValue('HIPAYPAYMENTS_OS_CHARGEBACK');
        $this->migrationPageDisplayed = (bool) \Configuration::getGlobalValue(self::PS_CONFIG_KEY_MIGRATION_PAGE_DISPLAYED);

        return $this;
    }

    /**
     * @param string $type
     * @return PublicCredentials
     */
    public function getPublicCredentials(string $type = Credentials::CREDENTIALS_TYPE_MAIN): PublicCredentials
    {
        $credentials = new PublicCredentials();
        if (true === $this->accountSettings->useDemoMode) {
            $credentials->identifiers = $this->demoCredentials->public;
            $credentials->env = $this->demoCredentials->env;
            $credentials->hashingAlgorithm = $this->demoCredentials->hashingAlgorithm;

            return $credentials;
        }

        switch ($type) {
            case Credentials::CREDENTIALS_TYPE_MAIN:
            default:
                if (AccountSettings::MODE_TEST === $this->accountSettings->environment) {
                    $credentials->env = \HiPay\Fullservice\HTTP\Configuration\Configuration::API_ENV_STAGE;
                    $credentials->identifiers = $this->accountSettings->testPublicIdentifiers;
                } else {
                    $credentials->env = \HiPay\Fullservice\HTTP\Configuration\Configuration::API_ENV_PRODUCTION;
                    $credentials->identifiers = $this->accountSettings->prodPublicIdentifiers;
                }
                break;
            case Credentials::CREDENTIALS_TYPE_APPLE_PAY:
                if (AccountSettings::MODE_TEST === $this->accountSettings->environment) {
                    if (!$this->accountSettings->applePayTestPublicIdentifiers->username) {
                        return self::getPublicCredentials();
                    }
                    $credentials->env = \HiPay\Fullservice\HTTP\Configuration\Configuration::API_ENV_STAGE;
                    $credentials->identifiers = $this->accountSettings->applePayTestPublicIdentifiers;
                } else {
                    if (!$this->accountSettings->applePayProdPublicIdentifiers->username) {
                        return self::getPublicCredentials();
                    }
                    $credentials->env = \HiPay\Fullservice\HTTP\Configuration\Configuration::API_ENV_PRODUCTION;
                    $credentials->identifiers = $this->accountSettings->applePayProdPublicIdentifiers;
                }
                break;
            case Credentials::CREDENTIALS_TYPE_MOTO:
                return self::getPublicCredentials();
        }

        return $credentials;
    }

    /**
     * @param string $type
     * @return PrivateCredentials
     */
    public function getPrivateCredentials(string $type = Credentials::CREDENTIALS_TYPE_MAIN): PrivateCredentials
    {
        $credentials = new PrivateCredentials();
        if (true === $this->accountSettings->useDemoMode) {
            $credentials->identifiers = $this->demoCredentials->private;
            $credentials->env = $this->demoCredentials->env;
            $credentials->hashingAlgorithm = $this->demoCredentials->hashingAlgorithm;

            return $credentials;
        }

        switch ($type) {
            case Credentials::CREDENTIALS_TYPE_MAIN:
            default:
                if (AccountSettings::MODE_TEST === $this->accountSettings->environment) {
                    $credentials->env = \HiPay\Fullservice\HTTP\Configuration\Configuration::API_ENV_STAGE;
                    $credentials->identifiers = $this->accountSettings->testPrivateIdentifiers;
                } else {
                    $credentials->env = \HiPay\Fullservice\HTTP\Configuration\Configuration::API_ENV_PRODUCTION;
                    $credentials->identifiers = $this->accountSettings->prodPrivateIdentifiers;
                }
                $credentials->hashingAlgorithm = $this->accountSettings->hashingAlgorithms[Credentials::CREDENTIALS_TYPE_MAIN] ?? self::DEFAULT_HASHING_ALGORITHM;
                break;
            case Credentials::CREDENTIALS_TYPE_APPLE_PAY:
                if (AccountSettings::MODE_TEST === $this->accountSettings->environment) {
                    if (!$this->accountSettings->applePayTestPrivateIdentifiers->username) {
                        return self::getPrivateCredentials();
                    }
                    $credentials->env = \HiPay\Fullservice\HTTP\Configuration\Configuration::API_ENV_STAGE;
                    $credentials->identifiers = $this->accountSettings->applePayTestPrivateIdentifiers;
                } else {
                    if (!$this->accountSettings->applePayProdPrivateIdentifiers->username) {
                        return self::getPrivateCredentials();
                    }
                    $credentials->env = \HiPay\Fullservice\HTTP\Configuration\Configuration::API_ENV_PRODUCTION;
                    $credentials->identifiers = $this->accountSettings->applePayProdPrivateIdentifiers;
                }
                $credentials->hashingAlgorithm = $this->accountSettings->hashingAlgorithms[Credentials::CREDENTIALS_TYPE_APPLE_PAY] ?? self::DEFAULT_HASHING_ALGORITHM;
                break;
            case Credentials::CREDENTIALS_TYPE_MOTO:
                if (AccountSettings::MODE_TEST === $this->accountSettings->environment) {
                    if (!$this->accountSettings->motoTestPrivateIdentifiers->username) {
                        return self::getPrivateCredentials();
                    }
                    $credentials->env = \HiPay\Fullservice\HTTP\Configuration\Configuration::API_ENV_STAGE;
                    $credentials->identifiers = $this->accountSettings->motoTestPrivateIdentifiers;
                } else {
                    if (!$this->accountSettings->motoProdPrivateIdentifiers->username) {
                        return self::getPrivateCredentials();
                    }
                    $credentials->env = \HiPay\Fullservice\HTTP\Configuration\Configuration::API_ENV_PRODUCTION;
                    $credentials->identifiers = $this->accountSettings->motoProdPrivateIdentifiers;
                }
                $credentials->hashingAlgorithm = $this->accountSettings->hashingAlgorithms[Credentials::CREDENTIALS_TYPE_MOTO] ?? self::DEFAULT_HASHING_ALGORITHM;
                break;
        }

        return $credentials;
    }
}
