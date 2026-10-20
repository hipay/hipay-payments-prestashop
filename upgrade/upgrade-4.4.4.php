<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @param HiPayPayments $module
 * @return bool
 */
function upgrade_module_4_4_4(HiPayPayments $module): bool
{
    /** @var HiPay\PrestaShop\Install\Installer $installer */
    $installer = $module->getService('hp.installer');
    $installer->getLogger()->info('Start of Upgrade module to 4.4.4');

    try {
        /** @var HiPay\PrestaShop\Settings\Settings $settings */
        $settings = $module->getService('hp.settings');
        $przelewy24 = $settings->otherPMSettings->findByCode('przelewy24');
        if (false !== $przelewy24 && true !== $przelewy24->canRefund) {
            $przelewy24->canRefund = true;
            /** @var HiPay\PrestaShop\Settings\Updater\OtherPMSettingsUpdater $otherPMSettingsUpdater */
            $otherPMSettingsUpdater = $module->getService('hp.settings.other_pm.updater');
            $otherPMSettingsUpdater->updateObject($settings->otherPMSettings);
        }
    } catch (Exception $e) {
        $installer->getLogger()->error($e->getMessage());
    }

    $installer->getLogger()->info('End of Upgrade module to 4.4.4');

    return true;
}
