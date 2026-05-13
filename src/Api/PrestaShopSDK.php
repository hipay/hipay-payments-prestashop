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

namespace HiPay\PrestaShop\Api;

use HiPay\PrestaShop\Logger\LoggerFactory;
use HiPay\PrestaShop\Settings\Settings;
use HiPay\PrestaShop\Settings\SettingsLoader;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class PrestaShopSDK
 */
class PrestaShopSDK
{
    /** @var \HiPayPayments */
    private $module;

    /**
     * PrestaShopSDK Constructor.
     *
     * @param \HiPayPayments $module
     */
    public function __construct(\HiPayPayments $module)
    {
        $this->module = $module;
    }

    /**
     * @param int|null $idShop
     * @param int|null $idShopGroup
     * @param string   $credentialsType
     * @return Credentials
     */
    public function init(int $idShop = null, int $idShopGroup = null, string $credentialsType = Credentials::CREDENTIALS_TYPE_MAIN): Credentials
    {
        /** @var SettingsLoader $settingsLoader */
        $settingsLoader = $this->module->getService('hp.settings.loader');
        /** @var Settings $settings */
        $settings = $settingsLoader->withContext($idShop, $idShopGroup, true);
        /** @var LoggerFactory $loggerFactory */
        $loggerFactory = $this->module->getService('hp.logger.factory');
        $logger = $loggerFactory
            ->withSettings($settings)
            ->withChannel('HiPayAPI');

        return new Credentials($settings, $logger, $credentialsType);
    }
}
