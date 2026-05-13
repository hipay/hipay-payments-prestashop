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

use AG\PSModuleUtils\Presenter\PresenterInterface;
use Alcohol\ISO4217;
use HiPay\PrestaShop\Settings\Entity\AccountSettings;
use HiPay\PrestaShop\Settings\Entity\APM\Multibanco;
use HiPay\PrestaShop\Settings\Entity\CardPaymentSettings;
use HiPay\PrestaShop\Settings\Entity\MainSettings;
use HiPay\PrestaShop\Settings\Settings;
use HiPay\PrestaShop\Settings\SettingsLoader;
use HiPay\PrestaShop\Utils\Tools;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class SettingsPresenter
 */
class SettingsPresenter implements PresenterInterface
{
    /** @var \HiPayPayments */
    private $module;

    /**
     * SettingsPresenter Constructor.
     *
     * @param \HiPayPayments $module
     */
    public function __construct(\HiPayPayments $module)
    {
        $this->module = $module;
    }

    /**
     * @param SettingsLoader $object
     * @return mixed[]
     * @throws \PrestaShopDatabaseException
     */
    public function present($object): array
    {
        $settingArray = $object->normalize();
        $settings = $object->load();

        /** @var \Context $context */
        $context = \Context::getContext();
        /** @var \Currency $defaultCurrency */
        $defaultCurrency = \Currency::getDefaultCurrency();
        $paymentCurrencies = Tools::getPaymentCurrencies($this->module->id, $context->language->id);
        $paymentCountries = Tools::getPaymentCountries($this->module->id, $context->language->id);
        $notifyCronParams = ['action' => 'processQueue', 'key' => \Configuration::getGlobalValue(Settings::PS_CONFIG_KEY_MODULE_UUID)];

        $settingArray['moduleInfo']['newVersionAvailable'] = version_compare($settingArray['moduleInfo']['latestVersionAvailable'], $this->module->version, '>');
        $settingArray['extra'] = [
            'moduleVersion' => $this->module->version,
            'path' => [
                'module' => sprintf(__PS_BASE_URI__.'modules/%s/', $this->module->name),
                'img' => sprintf(__PS_BASE_URI__.'modules/%s/views/img/', $this->module->name),
            ],
            'urls' => [
                'notifyCron' => \Context::getContext()->link->getModuleLink((string) $this->module->name, 'notifycron', $notifyCronParams),
            ],
            'countries' => $paymentCountries,
            'currencies' => [
                'list' => $paymentCurrencies,
                'defaultIso' => $defaultCurrency->iso_code,
                'defaultIsoDecimals' => $defaultCurrency->precision,
            ],
            'languages' => \Language::getLanguages(),
            'unavailableCards' => $settings->cardPaymentSettings->listUnavailableCards(),
            'unavailableAPM' => $settings->otherPMSettings->listUnavailableAPM(),
            'const' => [
                'ENV_TEST' => AccountSettings::MODE_TEST,
                'ENV_PRODUCTION' => AccountSettings::MODE_PRODUCTION,
                'DISPLAY_MODE_HOSTED_FIELDS' => CardPaymentSettings::DISPLAY_MODE_HOSTED_FIELDS,
                'DISPLAY_MODE_HOSTED_PAGE' => CardPaymentSettings::DISPLAY_MODE_HOSTED_PAGE,
                'HOSTED_PAGE_TYPE_REDIRECT' => CardPaymentSettings::HOSTED_PAGE_TYPE_REDIRECT,
                'HOSTED_PAGE_TYPE_IFRAME' => CardPaymentSettings::HOSTED_PAGE_TYPE_IFRAME,
                'CAPTURE_AUTO' => MainSettings::CAPTURE_MODE_AUTO,
                'CAPTURE_MANUAL' => MainSettings::CAPTURE_MODE_MANUAL,
                'THREE_DS_MODES' => [
                    CardPaymentSettings::THREE_DS_MODE_DISABLED => $this->module->l('Disabled / Bypass 3-D Secure Authentication', 'SettingsPresenter'),
                    CardPaymentSettings::THREE_DS_MODE_IF_AVAILABLE => $this->module->l('3-D Secure authentication if available', 'SettingsPresenter'),
                    CardPaymentSettings::THREE_DS_MODE_ALWAYS => $this->module->l('Always / 3-D Secure authentication mandatory', 'SettingsPresenter'),
                ],
                'MULTIBANCO' => [
                    'EXPIRATION_LIMITS' => Multibanco::EXPIRATION_LIMITS,
                ],
            ],
            'links' => [
                'paymentPreferences' => \Context::getContext()->link->getAdminLink('AdminPaymentPreferences'),
                'testCardsUrl' => 'https://support.hipay.com/hc/fr/articles/213882649-Comment-tester-les-m%C3%A9thodes-de-paiement',
            ],
        ];
        foreach ($settingArray['otherPMSettings']['paymentMethods'] as $key => $pm) {
            if ($pm['currenciesForced']) {
                foreach ($pm['currencies'] as $currencyCode) {
                    $id = \Currency::getIdByIsoCode($currencyCode);
                    if (!$id) {
                        $iso4217 = new ISO4217();
                        $currencyDetails = $iso4217->getByCode($currencyCode);
                        $settingArray['otherPMSettings']['paymentMethods'][$key]['missingCurrencies'][] = sprintf('%s - %s', $currencyDetails['name'], $currencyDetails['alpha3']);
                    }
                }
            }
            if ($pm['countriesForced']) {
                foreach ($pm['countries'] as $countryCode) {
                    $country = new \Country((int) \Country::getByIso($countryCode), $context->language->id);
                    if (!\Validate::isLoadedObject($country)) {
                        $settingArray['otherPMSettings']['paymentMethods'][$key]['missingCountries'][] = $countryCode;
                    } elseif (!$country->active) {
                        $settingArray['otherPMSettings']['paymentMethods'][$key]['missingCountries'][] = $country->name;
                    }
                }
            }
        }

        return $settingArray;
    }
}
