<?php
/**
 * 2026 HiPay
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0).
 * It is also available through the world-wide-web at this URL: https://opensource.org/licenses/AFL-3.0
 *
 * @author    HiPay partner
 * @copyright 2026
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

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

        $rows = Db::getInstance()->executeS(
            (new DbQuery())
                ->select('id_configuration, value')
                ->from('configuration')
                ->where(sprintf('name = "%s"', HiPay\PrestaShop\Settings\Settings::PS_CONFIG_KEY_OTHER_PM))
        );

        foreach ($rows as $row) {
            $otherPMSettings = json_decode($row['value'], true);
            if (!is_array($otherPMSettings) || empty($otherPMSettings['paymentMethods'])) {
                continue;
            }

            $updated = false;
            foreach ($otherPMSettings['paymentMethods'] as &$paymentMethod) {
                if (isset($paymentMethod['code'], $paymentMethod['name'])
                    && 'przelewy24' === $paymentMethod['code']
                    && 'Blik / Przelewy24' !== $paymentMethod['name']
                ) {
                    $paymentMethod['name'] = 'Blik / Przelewy24';
                    $updated = true;
                }
            }
            unset($paymentMethod);

            if ($updated) {
                Db::getInstance()->update(
                    'configuration',
                    ['value' => json_encode($otherPMSettings)],
                    sprintf('id_configuration = %d', (int) $row['id_configuration'])
                );
            }
        }
    } catch (Exception $e) {
        $installer->getLogger()->error($e->getMessage());
    }

    $installer->getLogger()->info('End of Upgrade module to 4.4.4');

    return true;
}
