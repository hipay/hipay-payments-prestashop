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

namespace HiPay\PrestaShop\Presenter;

use Alcohol\ISO4217;
use HiPay\PrestaShop\Api\Credentials;
use HiPay\PrestaShop\Settings\Entity\AccountSettings;
use HiPay\PrestaShop\Settings\Settings;
use PrestaShop\PrestaShop\Adapter\Presenter\PresenterInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class HealthCheckPresenter
 */
class HealthCheckPresenter implements PresenterInterface
{
    /** @var \HiPayPayments */
    private $module;

    /** @var Settings */
    private $settings;

    /** @var \Context */
    private $context;

    /**
     * HealthCheckPresenter Constructor.
     *
     * @param \HiPayPayments $module
     * @param Settings       $settings
     * @param \Context       $context
     */
    public function __construct(
        \HiPayPayments $module,
        Settings       $settings,
        \Context       $context
    ) {
        $this->module = $module;
        $this->settings = $settings;
        $this->context = $context;
    }

    /**
     * @param mixed $object
     * @return mixed[]
     */
    public function present($object = null): array
    {
        $dbQueryNotificationsQueue = (new \DbQuery())
            ->select('COUNT(*) as count')
            ->from('hipaypayments_queued_notification')
            ->where('is_processed = 0');
        $notificationsCount = \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($dbQueryNotificationsQueue);
        $hookableHooks = $this->module->getPossibleHooksList();
        $hookableHooksCount = count($hookableHooks);
        $unregisteredHooks = array_filter($hookableHooks, function ($hook) {
            return $hook['registered'] === false;
        });
        $registeredHooksCount = $hookableHooksCount - count($unregisteredHooks);
        $orderStateIds = [
            $this->settings->chargebackStatusId,
            $this->settings->partiallyCapturedStatusId,
            $this->settings->partiallyRefundedStatusId,
            $this->settings->pendingAuthStatusId,
            $this->settings->pendingCaptureStatusId,
        ];
        $validOrderStatesTotal = 0;
        foreach ($orderStateIds as $id) {
            $orderState = new \OrderState((int) $id);
            if (\Validate::isLoadedObject($orderState)) {
                if (!$orderState->deleted) {
                    $validOrderStatesTotal++;
                }
            }
        }

        /** @var \HiPay\PrestaShop\Api\PrestaShopSDK $sdk */
        $sdk = $this->module->getService('hp.sdk.gateway');
        $credentialsErrors = [];
        $credentialsApplePayErrors = [];
        $credentialsMotoErrors = [];
        $credentialsSuccessMessages = [];
        $credentialsApplePaySuccessMessages = [];
        $credentialsMotoSuccessMessages = [];
        try {
            $sdk->init()
                ->client()
                ->requestSecuritySettings();
            $credentialsSuccessMessages[] = $this->module->l('Public credentials are valid.', 'HealthCheckPresenter');
        } catch (\Exception $e) {
            $credentialsErrors[] = $this->module->l('Public credentials are not valid.', 'HealthCheckPresenter');
        }
        try {
            $sdk->init()
                ->server()
                ->requestSecuritySettings();
            $credentialsSuccessMessages[] = $this->module->l('Private credentials are valid.', 'HealthCheckPresenter');
        } catch (\Exception $e) {
            $credentialsErrors[] = $this->module->l('Private credentials are not valid.', 'HealthCheckPresenter');
        }
        $applePayCredentialsConfigured = false;
        if ($this->settings->accountSettings->isApplePayCredentialsConfigured(Credentials::CREDENTIALS_ACCESSIBILITY_PUBLIC)) {
            $applePayCredentialsConfigured = true;
            try {
                $sdk->init(null, null, Credentials::CREDENTIALS_TYPE_APPLE_PAY)
                    ->client()
                    ->requestSecuritySettings();
                $credentialsApplePaySuccessMessages[] = $this->module->l('Public credentials are valid.', 'HealthCheckPresenter');
            } catch (\Exception $e) {
                $credentialsMotoErrors[] = $this->module->l('Public credentials are not valid.', 'HealthCheckPresenter');
            }
        } else {
            $credentialsApplePaySuccessMessages[] = $this->module->l('Public credentials are not configured.', 'HealthCheckPresenter');
        }
        if ($this->settings->accountSettings->isApplePayCredentialsConfigured(Credentials::CREDENTIALS_ACCESSIBILITY_PRIVATE)) {
            $applePayCredentialsConfigured = true;
            try {
                $sdk->init(null, null, Credentials::CREDENTIALS_TYPE_APPLE_PAY)
                    ->server()
                    ->requestSecuritySettings();
                $credentialsApplePaySuccessMessages[] = $this->module->l('Private credentials are valid.', 'HealthCheckPresenter');
            } catch (\Exception $e) {
                $credentialsApplePayErrors[] = $this->module->l('Private credentials are not valid.', 'HealthCheckPresenter');
            }
        } else {
            $credentialsApplePaySuccessMessages[] = $this->module->l('Private credentials are not configured.', 'HealthCheckPresenter');
        }
        $motoCredentialsConfigured = false;
        if ($this->settings->accountSettings->motoCredentialsIsConfigured()) {
            $motoCredentialsConfigured = true;
            try {
                $sdk->init(null, null, Credentials::CREDENTIALS_TYPE_MOTO)
                    ->server()
                    ->requestSecuritySettings();
                $credentialsMotoSuccessMessages[] = $this->module->l('Private credentials are valid.', 'HealthCheckPresenter');
            } catch (\Exception $e) {
                $credentialsMotoErrors[] = $this->module->l('Private credentials are not valid.', 'HealthCheckPresenter');
            }
        } else {
            $credentialsMotoSuccessMessages[] = $this->module->l('Private credentials are not configured.', 'HealthCheckPresenter');
        }

        $missingCurrencies = [];
        $missingCountries = [];
        foreach ($this->settings->otherPMSettings->paymentMethods as $paymentMethod) {
            if (!$paymentMethod->enabled) {
                continue;
            }
            if ($paymentMethod->currenciesForced) {
                foreach ($paymentMethod->currencies as $currencyCode) {
                    $id = \Currency::getIdByIsoCode($currencyCode);
                    if (!$id) {
                        $iso4217 = new ISO4217();
                        $currencyDetails = $iso4217->getByCode($currencyCode);
                        $missingCurrencies[] = sprintf('%s - %s', $currencyDetails['name'], $currencyDetails['alpha3']);
                    }
                }
            }
            if ($paymentMethod->countriesForced) {
                foreach ($paymentMethod->countries as $countryCode) {
                    $country = new \Country((int) \Country::getByIso($countryCode), $this->context->language->id);
                    if (!\Validate::isLoadedObject($country)) {
                        $missingCountries[] = $countryCode;
                    } elseif (!$country->active) {
                        $missingCountries[] = $country->name;
                    }
                }
            }
        }
        $missingCurrencies = array_unique($missingCurrencies);
        $missingCountries = array_unique($missingCountries);
        $hashingAlgorithms = [
            Credentials::CREDENTIALS_TYPE_MAIN => [
                'title' => $this->module->l('Main', 'HealthCheckPresenter'),
                'value' => $this->settings->accountSettings->hashingAlgorithms[Credentials::CREDENTIALS_TYPE_MAIN]
            ],
        ];
        if ($this->settings->accountSettings->isApplePayCredentialsConfigured(Credentials::CREDENTIALS_ACCESSIBILITY_PRIVATE)) {
            $hashingAlgorithms[Credentials::CREDENTIALS_TYPE_APPLE_PAY] = [
                'title' => $this->module->l('ApplePay', 'HealthCheckPresenter'),
                'value' => $this->settings->accountSettings->hashingAlgorithms[Credentials::CREDENTIALS_TYPE_APPLE_PAY]
            ];
        }
        if ($this->settings->accountSettings->motoCredentialsIsConfigured()) {
            $hashingAlgorithms[Credentials::CREDENTIALS_TYPE_MOTO] = [
                'title' => $this->module->l('MOTO', 'HealthCheckPresenter'),
                'value' => $this->settings->accountSettings->hashingAlgorithms[Credentials::CREDENTIALS_TYPE_MOTO]
            ];
        }

        $checks = [
            [
                'title' => $this->module->l('Public / Private credentials checks', 'HealthCheckPresenter'),
                'value' => implode(' ', $credentialsErrors).implode(' ', $credentialsSuccessMessages),
                'icon' => count($credentialsErrors) ? 'icon-times-circle' : 'icon-check-circle',
            ]
        ];
        if ($applePayCredentialsConfigured) {
            $checks[] = [
                'title' => $this->module->l('ApplePay Public / Private credentials checks', 'HealthCheckPresenter'),
                'value' => implode(' ', $credentialsApplePayErrors).implode(' ', $credentialsApplePaySuccessMessages),
                'icon' => count($credentialsApplePayErrors) ? 'icon-times-circle' : 'icon-check-circle',
            ];
        }
        if ($motoCredentialsConfigured) {
            $checks[] = [
                'title' => $this->module->l('MOTO Private credentials checks', 'HealthCheckPresenter'),
                'value' => implode(' ', $credentialsMotoErrors).implode(' ', $credentialsMotoSuccessMessages),
                'icon' => count($credentialsMotoErrors) ? 'icon-times-circle' : 'icon-check-circle',
            ];
        }
        $checks[] = [
            'title' => $this->module->l('Order states', 'HealthCheckPresenter'),
            'value' => sprintf('%d %s / %d %s', $validOrderStatesTotal, $this->module->l('valid order state(s)', 'HealthCheckPresenter'), count($orderStateIds), $this->module->l('order states required', 'HealthCheckPresenter')),
            'icon' => $validOrderStatesTotal !== count($orderStateIds) ? 'icon-times-circle' : 'icon-check-circle',
        ];
        $checks[] = [
            'title' => $this->module->l('Hookable hooks', 'HealthCheckPresenter'),
            'value' => sprintf('%d %s / %d %s', $registeredHooksCount, $this->module->l('hook(s) registered', 'HealthCheckPresenter'), $hookableHooksCount, $this->module->l('hooks available', 'HealthCheckPresenter')),
            'icon' => count($unregisteredHooks) > 0 ? 'icon-times-circle' : 'icon-check-circle',
        ];
        $checks[] = [
            'title' => $this->module->l('Notifications queued', 'HealthCheckPresenter'),
            'value' => sprintf('%d %s', $notificationsCount, $this->module->l('unprocessed notification(s)', 'HealthCheckPresenter')),
            'icon' => $notificationsCount > 0 ? 'icon-warning' : 'icon-check-circle',
        ];
        $checks[] = [
            'title' => $this->module->l('Payment methods by country', 'HealthCheckPresenter'),
            'value' => sprintf('%d %s %s', count($missingCountries), $this->module->l('missing countries', 'HealthCheckPresenter'), count($missingCountries) > 0 ? sprintf('(%s)', implode(', ', array_filter($missingCountries, 'is_string'))) : ''),
            'icon' => count($missingCountries) > 0 ? 'icon-warning' : 'icon-check-circle',
        ];
        $checks[] = [
            'title' => $this->module->l('Payment methods by currencies', 'HealthCheckPresenter'),
            'value' => sprintf('%d %s %s', count($missingCurrencies), $this->module->l('missing currencies', 'HealthCheckPresenter'), count($missingCurrencies) > 0 ? sprintf('(%s)', implode(', ', array_filter($missingCurrencies, 'is_string'))) : ''),
            'icon' => count($missingCurrencies) ? 'icon-warning' : 'icon-check-circle',
        ];

        return [
            'basicInfo' => [
                'currentModuleVersion' => $this->module->version,
                'latestModuleVersion' => $this->settings->moduleInfo->latestVersionAvailable,
                'latestModuleUpdateCheck' => $this->settings->moduleInfo->dateLatestCheck,
                'phpVersion' => phpversion(),
                'psVersion' => _PS_VERSION_,
                'environment' => $this->settings->accountSettings->useDemoMode ? $this->module->l('Demo', 'HealthCheckPresenter') : ($this->settings->accountSettings->environment === AccountSettings::MODE_TEST ? $this->module->l('Test', 'HealthCheckPresenter') : $this->module->l('Production', 'HealthCheckPresenter')),
                'moduleHashingAlgorithms' => $hashingAlgorithms,
            ],
            'checks' => $checks,
        ];
    }
}
