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

use AG\PSModuleUtils\Exception\ExceptionList;
use HiPay\PrestaShop\Settings\Entity\AdvancedPaymentSettings;
use HiPay\PrestaShop\Settings\Entity\CardPaymentSettings;
use HiPay\PrestaShop\Settings\Updater\OtherPMSettingsUpdater;
use Symfony\Component\PropertyAccess\PropertyAccess;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class PaymentMethodsSync
 */
class PaymentMethodsSync
{
    /** @var \HiPayPayments */
    private $module;

    public function __construct(\HiPayPayments $module)
    {
        $this->module = $module;
    }

    /**
     * @return bool
     */
    public function updatePaymentMethodsList(): bool
    {
        /** @var \HiPay\PrestaShop\Logger\LoggerFactory $loggerFactory */
        $loggerFactory = $this->module->getService('hp.logger.factory');
        $logger = $loggerFactory->withChannel('PaymentMethodsSync');

        /** @var \HiPay\PrestaShop\Api\PrestaShopSDK $sdk */
        $sdk = $this->module->getService('hp.sdk.gateway');
        try {
            $availableProductsRequest = new \HiPay\Fullservice\Gateway\Request\Info\AvailablePaymentProductRequest();
            $availableProductsRequest->with_options = true;
            /** @var \HiPay\Fullservice\Gateway\Model\AvailablePaymentProduct[] $availableProducts */
            $availableProducts = $sdk
                ->init()
                ->server()
                ->requestAvailablePaymentProduct($availableProductsRequest);
        } catch (\Exception $e) {
            $logger->error($e->getMessage());

            return false;
        }
        if (!$availableProducts) {
            return true;
        }

        /** @var SettingsLoader $settingsLoader */
        $settingsLoader = $this->module->getService('hp.settings.loader');
        /** @var Settings $settings */
        $settings = $settingsLoader->load();

        $cardPaymentSettings = $settings->cardPaymentSettings;
        foreach ($availableProducts as $availableProduct) {
            if ($settings->cardPaymentSettings->hasCard($availableProduct->getCode())) {
                continue;
            }

            $paymentMethod = new \HiPay\PrestaShop\Settings\Entity\PaymentMethod();
            $paymentMethod->enabled = true;
            $paymentMethod->name = $availableProduct->getDescription();
            $paymentMethod->code = $availableProduct->getCode();
            $paymentMethod->currencies = [];
            $paymentMethod->countries = [];
            $paymentMethod->minAmount = 0;
            $paymentMethod->maxAmount = 0;
            $paymentMethod->canRefund = !in_array($availableProduct->getCode(), CardPaymentSettings::UNAUTHORIZED_REFUND);
            $cardPaymentSettings->paymentMethods[] = $paymentMethod;
        }
        foreach ($settings->cardPaymentSettings->paymentMethods as $k => $paymentMethod) {
            $codeExists = (bool) array_filter($availableProducts, function($product) use ($paymentMethod) {
                return $product->getCode() === $paymentMethod->code && isset(CardPaymentSettings::CARDS_PAYMENT_CODES[$paymentMethod->code]);
            });
            if (false === $codeExists) {
                unset($settings->cardPaymentSettings->paymentMethods[$k]);
            }
        }

        /** @var \HiPay\PrestaShop\Install\Installer $installer */
        $installer = $this->module->getService('hp.installer');
        foreach ($availableProducts as $availableProduct) {
            if ($settings->otherPMSettings->isInstalled($availableProduct->getCode()) || !$settings->otherPMSettings->canBeInstalled($availableProduct->getCode())) {
                continue;
            }

            try {
                if (in_array($availableProduct->getCode(), ['sisal', '3xcb', '4xcb', '3xcb-no-fees', '4xcb-no-fees'])) {
                    $displayName = AdvancedPaymentSettings::APM_CODES[$availableProduct->getCode()]['name'];
                } else {
                    $displayName = $availableProduct->getDescription();
                }
                $installer->installAPMByCode($availableProduct->getCode(), $displayName, $settings, $availableProduct);
            } catch (\Exception|\Symfony\Component\Serializer\Exception\ExceptionInterface $e) {
                $logger->error($e->getMessage());
                continue;
            }
        }
        if (!$settings->otherPMSettings->isInstalled('applepay')) {
            try {
                $installer->installAPMByCode('applepay', 'Apple Pay', $settings);
            } catch (\Exception|\Symfony\Component\Serializer\Exception\ExceptionInterface $e) {
                $logger->error($e->getMessage());
            }
        }

        $advancedPaymentSettings = $settings->otherPMSettings;
        foreach ($advancedPaymentSettings->paymentMethods as $k => $paymentMethod) {
            if ('applepay' === $paymentMethod->code) {
                continue;
            }
            $codeExists = (bool) array_filter($availableProducts, function($product) use ($paymentMethod) {
                return $product->getCode() === $paymentMethod->code;
            });
            if (false === $codeExists) {
                unset($advancedPaymentSettings->paymentMethods[$k]);
                continue;
            }

            if (in_array($paymentMethod->code, ['alma-3x', 'alma-4x'])) {
                $key = array_search($paymentMethod->code, array_map(
                    function($obj) {
                        return $obj->getCode();
                    },
                    $availableProducts
                ));
                if (false !== $key) {
                    $options = $availableProducts[$key]->getOptions();
                    if ($options) {
                        $advancedPaymentSettings->paymentMethods[$k]->minAmount = 'alma-3x' === $paymentMethod->code ? $options['basketAmountMin3x'] : $options['basketAmountMin4x'];
                        $advancedPaymentSettings->paymentMethods[$k]->maxAmount = 'alma-3x' === $paymentMethod->code ? $options['basketAmountMax3x'] : $options['basketAmountMax4x'];
                    }
                }
            }
        }

        /** @var OtherPMSettingsUpdater $apmUpdater */
        $apmUpdater = $this->module->getService('hp.settings.other_pm.updater');
        try {
            $apmUpdater->updateObject($advancedPaymentSettings);
        } catch (ExceptionList $e) {
            $logger->error('Error while updating APM settings', ['messages' => $e->getExceptionsMessages()]);
        }

        /** @var \HiPay\PrestaShop\Settings\Updater\CardPaymentSettingsUpdater $updater */
        $updater = $this->module->getService('hp.settings.card_payment.updater');
        try {
            $updater->updateObject($cardPaymentSettings);
        } catch (ExceptionList $e) {
            $logger->error('Error while updating card settings', ['messages' => $e->getExceptionsMessages()]);
            $propertyAccessor = PropertyAccess::createPropertyAccessor();
            $errors = [];
            foreach ($updater->getViolations() as $violation) {
                $propertyAccessor->setValue($errors, $violation->getPropertyPath(), '');
            }

            return false;
        }

        return true;
    }
}
