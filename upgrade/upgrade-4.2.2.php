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

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @param HiPayPayments $module
 * @return bool
 */
function upgrade_module_4_2_2(HiPayPayments $module): bool
{
    $dbQuery = (new DbQuery)
        ->select('id_configuration')
        ->from('configuration')
        ->where(sprintf('name = "%s"', HiPay\PrestaShop\Settings\Settings::PS_CONFIG_KEY_OTHER_PM));
    try {
        $exceptionMessage = false;
        $results = Db::getInstance()->executeS($dbQuery);
        if (!$results) {
            Configuration::updateGlobalValue(HiPay\PrestaShop\Settings\Settings::PS_CONFIG_KEY_OTHER_PM, '[]');
        }
    } catch (Exception $e) {
        $exceptionMessage = $e->getMessage();
    }

    /** @var HiPay\PrestaShop\Install\Installer $installer */
    $installer = $module->getService('hp.installer');
    $installer->getLogger()->info('Start of Upgrade module to 4.2.2');
    if (false !== $exceptionMessage) {
        $installer->getLogger()->error($exceptionMessage);
    }
    $installer->getYaml();
    $installer->installDb();
    $installer->registerHooks();
    /** @var HiPay\PrestaShop\Settings\Settings $settings */
    $settings = $module->getService('hp.settings');
    $orderStateIds = [$settings->pendingAuthStatusId, $settings->pendingCaptureStatusId, $settings->partiallyCapturedStatusId];
    foreach ($orderStateIds as $orderStateId) {
        try {
            $orderState = new OrderState((int) $orderStateId);
            if (!$orderState->deleted) {
                $orderState->logable = 1;
                $orderState->save();
            }
        } catch (Exception $e) {
            $installer->getLogger()->error($e->getMessage());
            continue;
        }
    }
    $installer->getLogger()->info('End of Upgrade module to 4.2.2');

    return true;
}
