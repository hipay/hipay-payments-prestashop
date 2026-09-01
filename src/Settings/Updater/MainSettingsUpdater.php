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

use AG\PSModuleUtils\Settings\AbstractSettings;
use AG\PSModuleUtils\Settings\AbstractSettingsUpdater;
use AG\PSModuleUtils\Settings\OptionsResolver\AbstractSettingsResolver;
use AG\PSModuleUtils\Settings\Validation\AbstractValidationData;
use HiPay\PrestaShop\Settings\Entity\CardPaymentSettings;
use HiPay\PrestaShop\Settings\Entity\MainSettings;
use HiPay\PrestaShop\Settings\Settings;
use Symfony\Component\Serializer\Serializer;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class MainSettingsUpdater
 * @property Settings $settings
 */
class MainSettingsUpdater extends AbstractSettingsUpdater
{
    /** @var CardPaymentSettingsUpdater */
    private $cardPaymentSettingsUpdater;

    /**
     * @param Serializer $serializer
     * @param AbstractSettingsResolver $resolver
     * @param Settings $settings
     * @param AbstractValidationData $validationData
     * @param \Module $module
     * @param CardPaymentSettingsUpdater $cardPaymentSettingsUpdater
     */
    public function __construct(
        Serializer $serializer,
        AbstractSettingsResolver $resolver,
        Settings $settings,
        AbstractValidationData $validationData,
        \Module $module,
        CardPaymentSettingsUpdater $cardPaymentSettingsUpdater
    ) {
        parent::__construct($serializer, $resolver, $settings, $validationData, $module);
        $this->cardPaymentSettingsUpdater = $cardPaymentSettingsUpdater;
    }

    /**
     * @param mixed[] $array
     * @return Settings
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @throws \AG\PSModuleUtils\Exception\ExceptionList
     */
    public function update(array $array): AbstractSettings
    {
        $settings = parent::update($array);

        if (true !== $this->settings->mainSettings->hostedPageEnabled
            && CardPaymentSettings::DISPLAY_MODE_HOSTED_PAGE === $this->settings->cardPaymentSettings->displayMode
        ) {
            $this->cardPaymentSettingsUpdater->update(['displayMode' => CardPaymentSettings::DISPLAY_MODE_HOSTED_FIELDS]);
        }

        return $settings;
    }

    /**
     * @param mixed[] $array
     * @return void
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    protected function denormalize($array)
    {
        $this->serializer->denormalize($array, MainSettings::class, null, ['object_to_populate' => $this->settings->mainSettings]);
    }

    /**
     * @return void
     */
    protected function serialize(): void
    {
        $this->json = $this->serializer->serialize($this->settings->mainSettings, 'json');
    }

    /**
     * @param int|null $idShop
     * @param int|null $idShopGroup
     * @return void
     */
    protected function save(int $idShop = null, int $idShopGroup = null): void
    {
        \Configuration::updateValue(Settings::PS_CONFIG_KEY_MAIN, $this->json, false, $idShopGroup, $idShop);
        if (\Shop::isFeatureActive() && \Shop::getContext() === \Shop::CONTEXT_ALL) {
            foreach (\Shop::getShops(true) as $shop) {
                \Configuration::updateValue(Settings::PS_CONFIG_KEY_MAIN, $this->json, false, (int) $shop['id_shop_group'], (int) $shop['id_shop']);
            }
        }
    }
}
