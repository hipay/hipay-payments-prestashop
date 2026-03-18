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

namespace HiPay\PrestaShop\Settings\Updater;

use AG\PSModuleUtils\Settings\AbstractSettingsUpdater;
use HiPay\PrestaShop\Settings\Entity\ModuleInfo;
use HiPay\PrestaShop\Settings\Settings;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class ModuleInfoUpdater
 * @property Settings $settings
 */
class ModuleInfoUpdater extends AbstractSettingsUpdater
{
    /**
     * @param mixed[] $array
     * @return void
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    protected function denormalize($array)
    {
        $this->serializer->denormalize($array, ModuleInfo::class, null, ['object_to_populate' => $this->settings->moduleInfo]);
    }

    /**
     * @return void
     */
    protected function serialize()
    {
        $this->json = $this->serializer->serialize($this->settings->moduleInfo, 'json');
    }

    /**
     * @param int|null $idShop
     * @param int|null $idShopGroup
     * @return void
     */
    protected function save(int $idShop = null, int $idShopGroup = null)
    {
        \Configuration::updateValue(Settings::PS_CONFIG_KEY_MODULE_INFO, $this->json, false, $idShopGroup, $idShop);
    }
}
