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

use AG\PSModuleUtils\Settings\AbstractSettingsLoader;
use HiPay\PrestaShop\Settings\Entity\AccountSettings;
use HiPay\PrestaShop\Settings\Entity\AdvancedPaymentSettings;
use HiPay\PrestaShop\Settings\Entity\CardPaymentSettings;
use HiPay\PrestaShop\Settings\Entity\MainSettings;
use HiPay\PrestaShop\Settings\Entity\ModuleInfo;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class SettingsLoader
 * @extends AbstractSettingsLoader<Settings>
 */
class SettingsLoader extends AbstractSettingsLoader
{
    /**
     * @return Settings
     */
    protected function deserialize(): Settings
    {
        $settings = new Settings();
        $jsonAccountSettings = \Configuration::get(Settings::PS_CONFIG_KEY_ACCOUNT, null, $this->idShopGroup, $this->idShop) ? : '[]';
        $jsonMainSettings = \Configuration::get(Settings::PS_CONFIG_KEY_MAIN, null, $this->idShopGroup, $this->idShop) ? : '[]';
        $jsonCardPaymentSettings = \Configuration::get(Settings::PS_CONFIG_KEY_CARD_PAYMENT, null, $this->idShopGroup, $this->idShop) ? : '[]';
        $jsonOtherPMSettings = \Configuration::get(Settings::PS_CONFIG_KEY_OTHER_PM, null, $this->idShopGroup, $this->idShop) ? : '[]';
        $jsonModuleInfo = \Configuration::get(Settings::PS_CONFIG_KEY_MODULE_INFO, null, $this->idShopGroup, $this->idShop) ? : '[]';
        /** @var AccountSettings $accountSettings */
        $accountSettings = $this->serializer->deserialize($jsonAccountSettings, AccountSettings::class, 'json');
        /** @var MainSettings $mainSettings */
        $mainSettings = $this->serializer->deserialize($jsonMainSettings, MainSettings::class, 'json');
        /** @var CardPaymentSettings $cardPaymentSettings */
        $cardPaymentSettings = $this->serializer->deserialize($jsonCardPaymentSettings, CardPaymentSettings::class, 'json');
        /** @var AdvancedPaymentSettings $otherPMSettings */
        $otherPMSettings = $this->serializer->deserialize($jsonOtherPMSettings, AdvancedPaymentSettings::class, 'json');
        /** @var ModuleInfo $moduleInfo */
        $moduleInfo = $this->serializer->deserialize($jsonModuleInfo, ModuleInfo::class, 'json');

        $settings->accountSettings = $accountSettings;
        $settings->mainSettings = $mainSettings;
        $settings->cardPaymentSettings = $cardPaymentSettings;
        $settings->otherPMSettings = $otherPMSettings;
        $settings->moduleInfo = $moduleInfo;

        return $settings->postLoading();
    }
}
