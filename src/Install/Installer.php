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

namespace HiPay\PrestaShop\Install;

use AG\PSModuleUtils\Exception\ExceptionList;
use AG\PSModuleUtils\Installer\AbstractInstaller;
use AG\PSModuleUtils\Installer\OrderStateManager;
use AG\PSModuleUtils\Installer\TabManager;
use AG\PSModuleUtils\Logger\AbstractLoggerFactory;
use HiPay\Fullservice\Gateway\Model\AvailablePaymentProduct;
use HiPay\PrestaShop\Settings\Entity\AbstractAdvancedPaymentMethod;
use HiPay\PrestaShop\Settings\Settings;
use HiPay\PrestaShop\Settings\Updater\AccountSettingsUpdater;
use HiPay\PrestaShop\Settings\Updater\CardPaymentSettingsUpdater;
use HiPay\PrestaShop\Settings\Updater\MainSettingsUpdater;
use HiPay\PrestaShop\Settings\Updater\OtherPMSettingsUpdater;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Yaml\Parser;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Installer
 */
class Installer extends AbstractInstaller
{
    /** @var AccountSettingsUpdater */
    private $accountSettingsUpdater;

    /** @var MainSettingsUpdater */
    private $mainSettingsUpdater;

    /** @var CardPaymentSettingsUpdater */
    private $cardPaymentSettingsUpdater;

    /** @var OtherPMSettingsUpdater */
    private $otherPMSettingsUpdater;

    /**
     * Installer Constructor.
     * @param \HiPayPayments             $module
     * @param AbstractLoggerFactory      $loggerFactory
     * @param TabManager                 $tabManager
     * @param OrderStateManager          $orderStateManager
     * @param AccountSettingsUpdater     $accountSettingsUpdater
     * @param MainSettingsUpdater        $mainSettingsUpdater
     * @param CardPaymentSettingsUpdater $cardPaymentSettingsUpdater
     * @param OtherPMSettingsUpdater     $otherPMSettingsUpdater
     */
    public function __construct(
        \HiPayPayments             $module,
        AbstractLoggerFactory      $loggerFactory,
        TabManager                 $tabManager,
        OrderStateManager          $orderStateManager,
        AccountSettingsUpdater     $accountSettingsUpdater,
        MainSettingsUpdater        $mainSettingsUpdater,
        CardPaymentSettingsUpdater $cardPaymentSettingsUpdater,
        OtherPMSettingsUpdater     $otherPMSettingsUpdater
    ) {
        parent::__construct($module, $loggerFactory);
        $this->tabManager = $tabManager;
        $this->orderStateManager = $orderStateManager;
        $this->accountSettingsUpdater = $accountSettingsUpdater;
        $this->mainSettingsUpdater = $mainSettingsUpdater;
        $this->cardPaymentSettingsUpdater = $cardPaymentSettingsUpdater;
        $this->otherPMSettingsUpdater = $otherPMSettingsUpdater;
    }

    /**
     * @return true
     * @throws \Exception
     */
    public function checkTechnicalRequirements(): bool
    {
        //@formatter:off
        if (!extension_loaded('curl')) {
            throw new \Exception($this->module->l('You need to enable the cURL extension to use this module.', 'Installer'));
        }
        if (version_compare(PHP_VERSION, '7.2.5', '<')) {
            throw new \Exception($this->module->l('PHP version 7.2.5 is required to run the module properly.', 'Installer'));
        }
        //@formatter:on

        return true;
    }

    /**
     * @return void
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function installTabs()
    {
        $this->getLogger()->info('Creating menus');
        $this->tabManager->setLogger($this->getLogger());
        $this->tabManager->installTabs($this->defaults['menus'], (string) $this->module->name);
    }

    /**
     * @return void
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function installOrderStates()
    {
        $this->getLogger()->info('Creating order states');
        $this->orderStateManager->setLogger($this->getLogger());
        $this->orderStateManager->installOrderStates($this->defaults['orderStatuses'], (string) $this->module->name);
    }

    /**
     * @return void
     * @throws ExceptionList
     * @throws \Exception
     */
    public function applyDefaultConfiguration()
    {
        $this->getLogger()->info('Applying default configuration');
        $this->accountSettingsUpdater->update($this->defaults['configuration']['account']);
        $this->mainSettingsUpdater->update($this->defaults['configuration']['main']);
        $this->cardPaymentSettingsUpdater->update($this->defaults['configuration']['cardPaymentSettings']);
        $this->otherPMSettingsUpdater->update($this->defaults['configuration']['otherPaymentMethods']);
        \Configuration::updateGlobalValue(Settings::PS_CONFIG_KEY_MODULE_UUID, Uuid::uuid4()->toString());
        \Configuration::updateGlobalValue(Settings::PS_CONFIG_KEY_MIGRATION_PAGE_DISPLAYED, true);
    }

    /**
     * @param mixed[] $apm
     * @param Parser $parser
     * @return AbstractAdvancedPaymentMethod
     * @throws ExceptionInterface
     */
    public function installAPM(array $apm, Parser $parser): AbstractAdvancedPaymentMethod
    {
        $apmConfiguration = $parser->parse((string) file_get_contents(sprintf('%s/install/apm_subfiles/%s', $this->module->getLocalPath(), $apm['file'])));
        $fullClassName = sprintf('%s\\%s', 'HiPay\\PrestaShop\\Settings\\Entity\\APM', $apmConfiguration['className']);

        return  $this->otherPMSettingsUpdater->denormalizeAPM($apmConfiguration['defaults'], $fullClassName);
    }

    /**
     * @param string $code
     * @param string $name
     * @param Settings $settings
     * @param AvailablePaymentProduct|null $availablePaymentProduct
     * @return void
     * @throws ExceptionInterface
     * @throws ExceptionList
     */
    public function installAPMByCode(string $code, string $name, Settings $settings, AvailablePaymentProduct $availablePaymentProduct = null): void
    {
        $this->getYaml();
        $parser = new Parser();
        $apmConfiguration = array_filter($this->defaults['configuration']['apmFilesList'], function($item) use ($code) {
            return $item['code'] === $code;
        });
        if (!$apmConfiguration) {
            throw new \Exception(sprintf('Advanced payment method %s not found', $code));
        }

        $apm = $this->installAPM(reset($apmConfiguration), $parser);
        $apm->name = $name;
        $apm->position = count($settings->otherPMSettings->paymentMethods) + 1;
        if (null !== $availablePaymentProduct && in_array($code, ['alma-3x', 'alma-4x'])) {
            $options = $availablePaymentProduct->getOptions();
            if ($options) {
                $apm->minAmount = 'alma-3x' === $code ? $options['basketAmountMin3x'] : $options['basketAmountMin4x'];
                $apm->maxAmount = 'alma-3x' === $code ? $options['basketAmountMax3x'] : $options['basketAmountMax4x'];
            }
        }
        $settings->otherPMSettings->paymentMethods[] = $apm;
        $this->otherPMSettingsUpdater->updateObject($settings->otherPMSettings);
    }
}
