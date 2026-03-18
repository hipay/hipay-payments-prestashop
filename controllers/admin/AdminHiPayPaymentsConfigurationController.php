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

use AG\PSModuleUtils\Exception\ExceptionList;
use HiPay\PrestaShop\Settings\Entity\CardPaymentSettings;
use Symfony\Component\PropertyAccess\PropertyAccess;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class AdminHiPayPaymentsConfigurationController
 */
class AdminHiPayPaymentsConfigurationController extends ModuleAdminController
{
    const TAB_INTRO = 'intro';
    const TAB_ACCOUNT = 'accountSettings';
    const TAB_MAIN_SETTINGS = 'mainSettings';
    const TAB_CARD_SETTINGS = 'cardPaymentSettings';
    const TAB_OTHER_PM = 'otherPMSettings';

    /** @var HiPayPayments */
    public $module;

    /** @var mixed[] */
    private $tabs;

    /** @var string */
    private $activeTab;

    /** @var mixed[] */
    private $postedData;

    /**
     * AdminHiPayConfigurationController Constructor.
     *
     * @throws PrestaShopException
     */
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
        $this->tabs = [
            self::TAB_INTRO => [
                'active' => false,
                'title' => $this->l('Intro'),
                'filename' => './_intro.tpl',
            ],
            self::TAB_ACCOUNT => [
                'active' => false,
                'title' => $this->l('My account'),
                'filename' => './_accountSettings.tpl',
                'icon' => 'icon-user',
            ],
            self::TAB_MAIN_SETTINGS => [
                'active' => false,
                'title' => $this->l('Main settings'),
                'filename' => './_mainSettings.tpl',
                'icon' => 'icon-cogs',
            ],
            self::TAB_CARD_SETTINGS => [
                'active' => false,
                'title' => $this->l('Card payment'),
                'filename' => './_cardPaymentSettings.tpl',
                'icon' => 'icon-credit-card',
            ],
            self::TAB_OTHER_PM => [
                'active' => false,
                'title' => $this->l('Other payment methods'),
                'filename' => './_otherPMSettings.tpl',
                'icon' => 'icon-credit-card',
            ],
        ];
        $this->activeTab = self::TAB_INTRO;
    }

    /**
     * @param bool $isNewTheme
     * @return void
     */
    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        Media::addJsDef([
            'hpAjaxToken' => Tools::getAdminTokenLite('AdminHiPayPaymentsAjax'),
            'hpAjaxController' => $this->context->link->getAdminLink('AdminHiPayPaymentsAjax'),
            'hpMigrationTranslations' => [
                'statusProcessing' => $this->module->l('Processing', 'AdminHiPayPaymentsConfigurationController'),
                'statusDone' => $this->module->l('Done', 'AdminHiPayPaymentsConfigurationController'),
                'statusError' => $this->module->l('Error', 'AdminHiPayPaymentsConfigurationController'),
            ],
        ]);
        $this->context->controller->addCSS([$this->module->getPathUri().'/views/css/config.min.css']);
        $this->context->controller->addJS([$this->module->getPathUri().'/views/js/admin-utils.js']);
        $this->context->controller->addJS([$this->module->getPathUri().'/views/js/config.js']);
        $this->context->controller->addJS([$this->module->getPathUri().'/views/js/bootstrap-multiselect.js']);
        $this->context->controller->addJS([$this->module->getPathUri().'/views/js/admin-data-migration.js']);
        $this->context->controller->addJqueryPlugin('colorpicker');
    }

    /**
     * @return void
     * @throws SmartyException
     */
    public function setModals(): void
    {
        $this->context->smarty->assign([
            'loader' => $this->module->getPathUri().'/views/img/icons/loader.svg',
        ]);
        $this->modals[] = [
            'modal_id' => 'js-hipay-payments-health-check-modal',
            'modal_class' => 'modal-lg hipay-payments-health-check-modal',
            'modal_title' => $this->module->l('Module Health Check', 'AdminHiPayPaymentsConfigurationController'),
            'modal_content' => $this->createTemplate('modal/_loading.tpl')->fetch(),
        ];
    }

    /**
     * @return void
     * @throws SmartyException
     * @throws PrestaShopDatabaseException
     */
    public function initContent()
    {
        $this->setModals();
        /** @var \HiPay\PrestaShop\Settings\SettingsLoader $settingsLoader */
        $settingsLoader = $this->module->getService('hp.settings.loader');
        /** @var HiPay\PrestaShop\Settings\Settings $settings */
        $settings = $settingsLoader->load();
        $this->handleModuleUpdateDetails($settings);
        if (true === $settings->migrationPageDisplayed && true === (bool) Module::isEnabled('hipay_enterprise')) {
            $this->context->smarty->assign(['displayMigrationContent' => true]);
        } else {
            /** @var \HiPay\PrestaShop\Settings\PaymentMethodsSync $paymentMethodsSync */
            $paymentMethodsSync = $this->module->getService('hp.settings.payment_methods_sync');
            $paymentMethodsSync->updatePaymentMethodsList();
            /** @var \HiPay\PrestaShop\Settings\SettingsLoader $settingsLoader */
            $settingsLoader = $this->module->getService('hp.settings.loader');
        }
        /** @var \HiPay\PrestaShop\Presenter\SettingsPresenter $presenter */
        $presenter = $this->module->getService('hp.settings.presenter');
        $data = $presenter->present($settingsLoader);
        $this->tabs[$this->activeTab]['active'] = true;
        $data['tabs'] = $this->tabs;
        if (!empty($this->postedData)) {
            $data = array_replace_recursive($data, $this->postedData);
        }
        $this->context->smarty->assign([
            'classPrefix' => 'hipay',
            'data' => $data,
            'languages' => $this->getLanguages(),
        ]);
        $this->content = $this->createTemplate('layout.tpl')->fetch();
        parent::initContent();
    }

    /**
     * @param \HiPay\PrestaShop\Settings\Settings $settings
     * @return void
     */
    public function handleModuleUpdateDetails(\HiPay\PrestaShop\Settings\Settings $settings)
    {
        if ($settings->moduleInfo->dateLatestCheck) {
            try {
                $latestCheckDate = new DateTime($settings->moduleInfo->dateLatestCheck);
                $currentDate = new DateTime();
            } catch (Exception $e) {
                return;
            }
            $interval = $latestCheckDate->diff($currentDate);

            if ((($interval->days * 24) + $interval->h) < 24) {
                return;
            }
        }

        $client = new Github\Client();
        /** @var Github\Api\Repo $repo */
        $repo = $client->api('repo');
        $releases = $repo->releases()->all('hipay', 'hipay-enterprise-sdk-prestashop');
        if ($releases) {
            foreach ($releases as $release) {
                $tag = $release['tag_name'];
                $branch = $release['target_commitish'];
                if ('develop' !== $branch) {
                    continue;
                }
                $settings->moduleInfo->dateLatestCheck = date('Y-m-d H:i:s');
                $settings->moduleInfo->latestVersionAvailable = $tag;
                $settings->moduleInfo->releaseUrl = $release['html_url'];
                $settings->moduleInfo->assetUrl = isset($release['assets']) && is_array($release['assets']) ? $release['assets'][0]['browser_download_url'] : '';
                /** @var \HiPay\PrestaShop\Settings\Updater\ModuleInfoUpdater $updater */
                $updater = $this->module->getService('hp.settings.module_info.updater');
                try {
                    $updater->updateObject($settings->moduleInfo);
                } catch (ExceptionList $e) {
                    return;
                }

                break;
            }
        }
    }

    /**
     * @return void
     */
    public function processSaveAccountForm()
    {
        $this->saveAccount();
        if (Tools::isSubmit('submitSaveCheckCredentials')) {
            $this->checkCredentials();
        }
    }

    /**
     * @return void
     */
    public function saveAccount()
    {
        $this->activeTab = self::TAB_ACCOUNT;
        /** @var \HiPay\PrestaShop\Settings\Updater\AccountSettingsUpdater $updater */
        $updater = $this->module->getService('hp.settings.account.updater');
        $form = Tools::getValue('hpAccountSettings');
        try {
            $updater->update($form);
        } catch (ExceptionList $e) {
            $propertyAccessor = PropertyAccess::createPropertyAccessor();
            $errors = [];
            foreach ($updater->getViolations() as $violation) {
                $propertyAccessor->setValue($errors, $violation->getPropertyPath(), '');
            }
            $this->postedData['accountSettings'] = array_diff_key($form, $errors);
            $this->errors += $e->getExceptionsMessages();

            return;
        }
        $this->updateHiPaySettings();
        //@formatter:off
        $this->confirmations[] = $this->module->l('Account settings saved successfully.', 'AdminHiPayPaymentsConfigurationController');
        //@formatter:on
    }

    /**
     * @return bool
     */
    public function checkCredentials(): bool
    {
        $this->activeTab = self::TAB_ACCOUNT;
        /** @var \HiPay\PrestaShop\Api\PrestaShopSDK $sdk */
        $sdk = $this->module->getService('hp.sdk.gateway');
        /** @var \HiPay\PrestaShop\Settings\SettingsLoader $settingsLoader */
        $settingsLoader = $this->module->getService('hp.settings.loader');
        /** @var HiPay\PrestaShop\Settings\Settings $settings */
        $settings = $settingsLoader->load();
        try {
            $sdk->init()
                ->client()
                ->requestSecuritySettings();
            $this->confirmations[] = $this->module->l('Public credentials are valid.', 'AdminHiPayPaymentsConfigurationController');
        } catch (Exception $e) {
            $this->errors[] = $this->module->l('Public credentials are not valid.', 'AdminHiPayPaymentsConfigurationController');
        }
        if ($settings->accountSettings->isApplePayCredentialsConfigured(\HiPay\PrestaShop\Api\Credentials::CREDENTIALS_ACCESSIBILITY_PUBLIC)) {
            try {
                $sdk->init(null, null, \HiPay\PrestaShop\Api\Credentials::CREDENTIALS_TYPE_APPLE_PAY)
                    ->client()
                    ->requestSecuritySettings();
                $this->confirmations[] = sprintf('ApplePay : %s', $this->module->l('Public credentials are valid.', 'AdminHiPayPaymentsConfigurationController'));
            } catch (Exception $e) {
                $this->errors[] = sprintf('ApplePay : %s', $this->module->l('Public credentials are not valid.', 'AdminHiPayPaymentsConfigurationController'));
            }
        }
        try {
            $sdk->init()
                ->server()
                ->requestSecuritySettings();
            $this->confirmations[] = $this->module->l('Private credentials are valid.', 'AdminHiPayPaymentsConfigurationController');
        } catch (Exception $e) {
            $this->errors[] = $this->module->l('Private credentials are not valid.', 'AdminHiPayPaymentsConfigurationController');
        }
        if ($settings->accountSettings->isApplePayCredentialsConfigured(\HiPay\PrestaShop\Api\Credentials::CREDENTIALS_ACCESSIBILITY_PRIVATE)) {
            try {
                $sdk->init(null, null, \HiPay\PrestaShop\Api\Credentials::CREDENTIALS_TYPE_APPLE_PAY)
                    ->server()
                    ->requestSecuritySettings();
                $this->confirmations[] = sprintf('ApplePay : %s', $this->module->l('Private credentials are valid.', 'AdminHiPayPaymentsConfigurationController'));
            } catch (Exception $e) {
                $this->errors[] = sprintf('ApplePay : %s', $this->module->l('Private credentials are not valid.', 'AdminHiPayPaymentsConfigurationController'));
            }
        }
        if ($settings->accountSettings->motoCredentialsIsConfigured()) {
            try {
                $sdk->init(null, null, \HiPay\PrestaShop\Api\Credentials::CREDENTIALS_TYPE_MOTO)
                    ->server()
                    ->requestSecuritySettings();
                $this->confirmations[] = sprintf('MOTO : %s', $this->module->l('Private credentials are valid.', 'AdminHiPayPaymentsConfigurationController'));
            } catch (Exception $e) {
                $this->errors[] = sprintf('MOTO : %s', $this->module->l('Private credentials are not valid.', 'AdminHiPayPaymentsConfigurationController'));
            }
        }
        if ($this->errors) {
            return false;
        }

        return true;
    }

    /**
     * @return void
     */
    public function updateHiPaySettings()
    {
        /** @var \HiPay\PrestaShop\Settings\SettingsLoader $settingsLoader */
        $settingsLoader = $this->module->getService('hp.settings.loader');
        /** @var HiPay\PrestaShop\Settings\Settings $settings */
        $settings = $settingsLoader->load();
        $accountSettings = $settings->accountSettings;

        /** @var \HiPay\PrestaShop\Api\PrestaShopSDK $sdk */
        $sdk = $this->module->getService('hp.sdk.gateway');
        $credentialsTypes = [
            \HiPay\PrestaShop\Api\Credentials::CREDENTIALS_TYPE_MAIN,
            \HiPay\PrestaShop\Api\Credentials::CREDENTIALS_TYPE_APPLE_PAY,
            \HiPay\PrestaShop\Api\Credentials::CREDENTIALS_TYPE_MOTO,
        ];
        foreach ($credentialsTypes as $credentialsType) {
            try {
                /** @var \HiPay\Fullservice\Gateway\Model\SecuritySettings $securitySettings */
                $securitySettings = $sdk->init(null, null, $credentialsType)
                    ->server()
                    ->requestSecuritySettings();
            } catch (Exception $e) {
                continue;
            }
            $hashingAlgorithm = $securitySettings->getHashingAlgorithm();
            $accountSettings->hashingAlgorithms[$credentialsType] = $hashingAlgorithm;
        }
        /** @var \HiPay\PrestaShop\Settings\Updater\AccountSettingsUpdater $updater */
        $updater = $this->module->getService('hp.settings.account.updater');
        try {
            $updater->updateObject($accountSettings);
        } catch (ExceptionList $e) {
            $propertyAccessor = PropertyAccess::createPropertyAccessor();
            $errors = [];
            foreach ($updater->getViolations() as $violation) {
                $propertyAccessor->setValue($errors, $violation->getPropertyPath(), '');
            }

            return;
        }
    }

    /**
     * @return void
     */
    public function processSaveMainSettingsForm()
    {
        $this->activeTab = self::TAB_MAIN_SETTINGS;
        /** @var \HiPay\PrestaShop\Settings\Updater\MainSettingsUpdater $updater */
        $updater = $this->module->getService('hp.settings.main.updater');
        $form = Tools::getValue('hpMainSettings');
        try {
            $updater->update($form);
        } catch (ExceptionList $e) {
            $propertyAccessor = PropertyAccess::createPropertyAccessor();
            $errors = [];
            foreach ($updater->getViolations() as $violation) {
                $propertyAccessor->setValue($errors, $violation->getPropertyPath(), '');
            }
            $this->errors += $e->getExceptionsMessages();

            return;
        }
        //@formatter:off
        $this->confirmations[] = $this->module->l('Main settings saved successfully.', 'AdminHiPayPaymentsConfigurationController');
        //@formatter:on
    }

    /**
     * @return void
     */
    public function processSaveCardPaymentSettingsForm()
    {
        if (true === $this->saveCardPaymentSettings()) {
            //@formatter:off
            $this->confirmations[] = $this->module->l('Card payment settings saved successfully.', 'AdminHiPayPaymentsConfigurationController');
            //@formatter:on
        }
    }

    /**
     * @return void
     */
    public function processSaveCardsForm()
    {
        if (true === $this->saveCardPaymentSettings()) {
            //@formatter:off
            $this->confirmations[] = $this->module->l('Cards settings saved successfully.', 'AdminHiPayPaymentsConfigurationController');
            //@formatter:on
        }
    }

    /**
     * @return bool
     */
    public function saveCardPaymentSettings(): bool
    {
        $this->activeTab = self::TAB_CARD_SETTINGS;
        /** @var \HiPay\PrestaShop\Settings\Updater\CardPaymentSettingsUpdater $updater */
        $updater = $this->module->getService('hp.settings.card_payment.updater');
        $form = Tools::getValue('hpCardPaymentSettings');
        try {
            $updater->update($form);
        } catch (ExceptionList $e) {
            $propertyAccessor = PropertyAccess::createPropertyAccessor();
            $errors = [];
            foreach ($updater->getViolations() as $violation) {
                $propertyAccessor->setValue($errors, $violation->getPropertyPath(), '');
            }
            $this->errors += $e->getExceptionsMessages();

            return false;
        }

        return true;
    }

    /**
     * @return void
     */
    public function processSaveAPMForm()
    {
        $this->activeTab = self::TAB_OTHER_PM;
        /** @var \HiPay\PrestaShop\Settings\Updater\OtherPMSettingsUpdater $updater */
        $updater = $this->module->getService('hp.settings.other_pm.updater');
        $form = Tools::getValue('hpAdvancedPaymentSettings');
        try {
            $updater->update($form);
        } catch (ExceptionList $e) {
            $propertyAccessor = PropertyAccess::createPropertyAccessor();
            $errors = [];
            foreach ($updater->getViolations() as $violation) {
                $propertyAccessor->setValue($errors, $violation->getPropertyPath(), '');
            }
            $this->errors += $e->getExceptionsMessages();

            return;
        }
        //@formatter:off
        $this->confirmations[] = $this->module->l('Advanced payment settings saved successfully.', 'AdminHiPayPaymentsConfigurationController');
        //@formatter:on
    }
}
