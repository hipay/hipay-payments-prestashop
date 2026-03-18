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

use HiPay\PrestaShop\Settings\Entity\AccountSettings;
use HiPay\PrestaShop\Settings\Entity\CardPaymentSettings;
use HiPay\PrestaShop\Settings\Entity\PrivateIdentifiers;
use HiPay\PrestaShop\Settings\Entity\PublicIdentifiers;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class AdminHiPayPaymentsAjaxController
 */
class AdminHiPayPaymentsAjaxController extends ModuleAdminController
{
    /** @var HiPayPayments $module */
    public $module;

    /**
     * @return void
     */
    public function displayAjaxGetTransactionDetails()
    {
        /** @var \HiPay\PrestaShop\Transaction\TransactionDetailsService $transactionDetailsService */
        $transactionDetailsService = $this->module->getService('hp.transaction_details.service');
        $data = Tools::getValue('data');
        try {
            $hipayPaymentsOrder = new HiPayPaymentsOrder((int) isset($data['idHiPayOrder']) ? $data['idHiPayOrder'] : 0);
            $data = $transactionDetailsService->retrieveTransactionDetails($hipayPaymentsOrder);
        } catch (Exception $e) {
            $data = [
                'success' => false,
                'errorMessage' => $e->getMessage(),
            ];
        }

        $this->context->smarty->assign([
            'data' => $data,
        ]);
        $html = $this->module->fetch(sprintf('module:hipaypayments/views/templates/admin/%s/_partials/displayAdminOrderContent.tpl', $this->module->theme));

        die(json_encode([
            'html_data' => $html,
        ]));
    }

    /**
     * @return void
     * @throws PrestaShopException
     * @throws Exception
     * @throws PrestaShopDatabaseException
     */
    public function displayAjaxGetOperationsModalContent()
    {
        $data = Tools::getValue('data');
        switch ($data['modalType']) {
            case 'full-capture':
            case 'partial-capture':
                /** @var \HiPay\PrestaShop\Presenter\AdminOperationPresenter $adminOperationPresenter */
                $adminOperationPresenter = $this->module->getService('hp.admin_operation.presenter');
                $this->context->smarty->assign([
                    'data' => $adminOperationPresenter->present(new Order((int) $data['idOrder']), $data),
                ]);
                $html = $this->module->fetch(sprintf('module:hipaypayments/views/templates/admin/%s/_partials/modalOperationsCaptureContent.tpl', $this->module->theme));
                break;
            case 'refund':
            default:
                /** @var \HiPay\PrestaShop\Presenter\AdminOperationPresenter $adminOperationPresenter */
                $adminOperationPresenter = $this->module->getService('hp.admin_operation.presenter');
                $this->context->smarty->assign([
                    'data' => $adminOperationPresenter->present(new Order((int) $data['idOrder']), $data),
                ]);
                $html = $this->module->fetch(sprintf('module:hipaypayments/views/templates/admin/%s/_partials/modalOperationsRefundContent.tpl', $this->module->theme));
                break;
        }

        die(json_encode([
            'html_data' => $html,
        ]));
    }

    /**
     * @return void
     */
    public function displayAjaxResetOperationsModal()
    {
        $html = $this->module->fetch(sprintf('module:hipaypayments/views/templates/admin/%s/_partials/modalOperationsLoadingContent.tpl', $this->module->theme));

        die(json_encode([
            'html_data' => $html,
        ]));
    }

    /**
     * @return void
     */
    public function displayAjaxGetHealthCheckModalContent()
    {
        /** @var \HiPay\PrestaShop\Presenter\HealthCheckPresenter $healthCheckPresenter */
        $healthCheckPresenter = $this->module->getService('hp.health_check.presenter');
        $data = $healthCheckPresenter->present();
        $this->context->smarty->assign([
            'data' => $data,
        ]);

        die(json_encode([
            'html_data' => $this->module->fetch('module:hipaypayments/views/templates/admin/hi_pay_payments_configuration/modal/_healthCheckContent.tpl'),
        ]));
    }

    /**
     * @return void
     */
    public function ajaxProcessMigrateCredentials()
    {
        Configuration::updateGlobalValue(\HiPay\PrestaShop\Settings\Settings::PS_CONFIG_KEY_MIGRATION_PAGE_DISPLAYED, false);
        /** @var \HiPay\PrestaShop\Logger\LoggerFactory $loggerFactory */
        $loggerFactory = $this->module->getService('hp.logger.factory');
        $logger = $loggerFactory->withChannel('MigrationCredentials');

        $logger->info('Start migration of credentials');
        try {
            $oldModule = \Module::getInstanceByName('hipay_enterprise');
            if (false !== $oldModule) {
                $oldModule->disable();
            }
        } catch (\Exception $e) {
            $logger->error($e->getMessage());
        }

        try {
            $oldConfig = json_decode((string) Configuration::get('HIPAY_CONFIG'), true);
            $modeTest = $oldConfig['account']['global']['sandbox_mode'] === "1";
            /** @var \HiPay\PrestaShop\Settings\Updater\AccountSettingsUpdater $updater */
            $updater = $this->module->getService('hp.settings.account.updater');
            $accountSettings = new AccountSettings();
            $accountSettings->useDemoMode = !$oldConfig['account']['sandbox']['api_username_sandbox'] && !$oldConfig['account']['production']['api_username_production'];
            $accountSettings->environment = $modeTest ? AccountSettings::MODE_TEST : AccountSettings::MODE_PRODUCTION;
            $accountSettings->testPrivateIdentifiers = new PrivateIdentifiers();
            $accountSettings->testPrivateIdentifiers->username = $oldConfig['account']['sandbox']['api_username_sandbox'];
            $accountSettings->testPrivateIdentifiers->password = $oldConfig['account']['sandbox']['api_password_sandbox'];
            $accountSettings->testPrivateIdentifiers->secret = $oldConfig['account']['sandbox']['api_secret_passphrase_sandbox'];
            $accountSettings->prodPrivateIdentifiers = new PrivateIdentifiers();
            $accountSettings->prodPrivateIdentifiers->username = $oldConfig['account']['production']['api_username_production'];
            $accountSettings->prodPrivateIdentifiers->password = $oldConfig['account']['production']['api_password_production'];
            $accountSettings->prodPrivateIdentifiers->secret = $oldConfig['account']['production']['api_secret_passphrase_production'];
            $accountSettings->testPublicIdentifiers = new PublicIdentifiers();
            $accountSettings->testPublicIdentifiers->username = $oldConfig['account']['sandbox']['api_tokenjs_username_sandbox'];
            $accountSettings->testPublicIdentifiers->password = $oldConfig['account']['sandbox']['api_tokenjs_password_publickey_sandbox'];
            $accountSettings->prodPublicIdentifiers = new PublicIdentifiers();
            $accountSettings->prodPublicIdentifiers->username = $oldConfig['account']['production']['api_tokenjs_username_production'];
            $accountSettings->prodPublicIdentifiers->password = $oldConfig['account']['production']['api_tokenjs_password_publickey_production'];

            $accountSettings->applePayTestPrivateIdentifiers = new PrivateIdentifiers();
            $accountSettings->applePayTestPrivateIdentifiers->username = $oldConfig['account']['sandbox']['api_apple_pay_username_sandbox'];
            $accountSettings->applePayTestPrivateIdentifiers->password = $oldConfig['account']['sandbox']['api_apple_pay_password_sandbox'];
            $accountSettings->applePayTestPrivateIdentifiers->secret = $oldConfig['account']['sandbox']['api_apple_pay_passphrase_sandbox'];
            $accountSettings->applePayProdPrivateIdentifiers = new PrivateIdentifiers();
            $accountSettings->applePayProdPrivateIdentifiers->username = $oldConfig['account']['production']['api_apple_pay_username_production'];
            $accountSettings->applePayProdPrivateIdentifiers->password = $oldConfig['account']['production']['api_apple_pay_password_production'];
            $accountSettings->applePayProdPrivateIdentifiers->secret = $oldConfig['account']['production']['api_apple_pay_passphrase_production'];
            $accountSettings->applePayTestPublicIdentifiers = new PublicIdentifiers();
            $accountSettings->applePayTestPublicIdentifiers->username = $oldConfig['account']['sandbox']['api_tokenjs_apple_pay_username_sandbox'];
            $accountSettings->applePayTestPublicIdentifiers->password = $oldConfig['account']['sandbox']['api_tokenjs_apple_pay_password_sandbox'];
            $accountSettings->applePayProdPublicIdentifiers = new PublicIdentifiers();
            $accountSettings->applePayProdPublicIdentifiers->username = $oldConfig['account']['production']['api_tokenjs_apple_pay_username_production'];
            $accountSettings->applePayProdPublicIdentifiers->password = $oldConfig['account']['production']['api_tokenjs_apple_pay_password_production'];

            $accountSettings->motoTestPrivateIdentifiers = new PrivateIdentifiers();
            $accountSettings->motoTestPrivateIdentifiers->username = $oldConfig['account']['sandbox']['api_moto_username_sandbox'];
            $accountSettings->motoTestPrivateIdentifiers->password = $oldConfig['account']['sandbox']['api_moto_password_sandbox'];
            $accountSettings->motoTestPrivateIdentifiers->secret = $oldConfig['account']['sandbox']['api_moto_secret_passphrase_sandbox'];
            $accountSettings->motoProdPrivateIdentifiers = new PrivateIdentifiers();
            $accountSettings->motoProdPrivateIdentifiers->username = $oldConfig['account']['production']['api_moto_username_production'];
            $accountSettings->motoProdPrivateIdentifiers->password = $oldConfig['account']['production']['api_moto_password_production'];
            $accountSettings->motoProdPrivateIdentifiers->secret = $oldConfig['account']['production']['api_moto_secret_passphrase_production'];

            if ($modeTest) {
                $accountSettings->hashingAlgorithms[\HiPay\PrestaShop\Api\Credentials::CREDENTIALS_TYPE_MAIN] = $oldConfig['account']['hash_algorithm']['test'];
                $accountSettings->hashingAlgorithms[\HiPay\PrestaShop\Api\Credentials::CREDENTIALS_TYPE_APPLE_PAY] = $oldConfig['account']['hash_algorithm']['test_apple_pay'];
                $accountSettings->hashingAlgorithms[\HiPay\PrestaShop\Api\Credentials::CREDENTIALS_TYPE_MOTO] = $oldConfig['account']['hash_algorithm']['test_moto'];
            } else {
                $accountSettings->hashingAlgorithms[\HiPay\PrestaShop\Api\Credentials::CREDENTIALS_TYPE_MAIN] = $oldConfig['account']['hash_algorithm']['production'];
                $accountSettings->hashingAlgorithms[\HiPay\PrestaShop\Api\Credentials::CREDENTIALS_TYPE_APPLE_PAY] = $oldConfig['account']['hash_algorithm']['production_apple_pay'];
                $accountSettings->hashingAlgorithms[\HiPay\PrestaShop\Api\Credentials::CREDENTIALS_TYPE_MOTO] = $oldConfig['account']['hash_algorithm']['production_moto'];
            }
        } catch (Exception $e) {
            $logger->error('Error while migrating credentials', ['errors' => $e->getMessage()]);
            die(json_encode(['success' => false, 'message' => $this->module->l('An error occurred. Please consult the logs.', 'AdminHiPayPaymentsAjaxController')]));
        }

        try {
            $updater->updateObject($accountSettings);
        } catch (\AG\PSModuleUtils\Exception\ExceptionList $e) {
            $logger->error('Error while migrating credentials', ['errors' => $e->getExceptionsMessages()]);
            die(json_encode(['success' => false, 'message' => $this->module->l('An error occurred. Please consult the logs.', 'AdminHiPayPaymentsAjaxController')]));
        }

        $logger->info('End of credentials migration');
        die(json_encode(['success' => true]));
    }

    /**
     * @return void
     */
    public function ajaxProcessUpdatePaymentMethods()
    {
        /** @var \HiPay\PrestaShop\Logger\LoggerFactory $loggerFactory */
        $loggerFactory = $this->module->getService('hp.logger.factory');
        $logger = $loggerFactory->withChannel('MigrationCredentials');

        $logger->info('Start update of payment methods');
        /** @var \HiPay\PrestaShop\Settings\PaymentMethodsSync $paymentMethodsSync */
        $paymentMethodsSync = $this->module->getService('hp.settings.payment_methods_sync');
        $paymentMethodsSync->updatePaymentMethodsList();

        $logger->info('End of payment methods update');
        die(json_encode(['success' => true]));
    }

    /**
     * @return void
     */
    public function ajaxProcessMigrateCardSettings()
    {
        /** @var \HiPay\PrestaShop\Logger\LoggerFactory $loggerFactory */
        $loggerFactory = $this->module->getService('hp.logger.factory');
        $logger = $loggerFactory->withChannel('MigrationCredentials');

        $logger->info('Start migration of card settings');
        $oldConfig = json_decode((string)Configuration::get('HIPAY_CONFIG'), true);
        /** @var \HiPay\PrestaShop\Settings\SettingsLoader $settingsLoader */
        $settingsLoader = $this->module->getService('hp.settings.loader');
        /** @var HiPay\PrestaShop\Settings\Settings $settings */
        $settings = $settingsLoader->load();
        $cardPaymentSettings = $settings->cardPaymentSettings;
        $cardPaymentSettings->displayMode = 'hosted_fields' === $oldConfig['payment']['global']['operating_mode']['UXMode'] ? CardPaymentSettings::DISPLAY_MODE_HOSTED_FIELDS : CardPaymentSettings::DISPLAY_MODE_HOSTED_PAGE;
        $cardPaymentSettings->hostedPageType = $oldConfig['payment']['global']['display_hosted_page'];
        $cardPaymentSettings->cancelButtonDisplayed = (bool)((int)$oldConfig['payment']['global']['display_cancel_button']);
        $cardPaymentSettings->oneClickEnabled = (bool)((int)$oldConfig['payment']['global']['card_token']);
        switch ($oldConfig['payment']['global']['activate_3d_secure']) {
            case 0:
                $cardPaymentSettings->threeDSMode = CardPaymentSettings::THREE_DS_MODE_DISABLED;
                break;
            case 1:
            case 2:
                $cardPaymentSettings->threeDSMode = CardPaymentSettings::THREE_DS_MODE_IF_AVAILABLE;
                break;
            case 3:
            case 4:
                $cardPaymentSettings->threeDSMode = CardPaymentSettings::THREE_DS_MODE_ALWAYS;
                break;
        }
        $cardPaymentSettings->UISettings->fontFamily = $oldConfig['payment']['global']['hosted_fields_style']['base']['fontFamily'];
        $cardPaymentSettings->UISettings->fontSize = $oldConfig['payment']['global']['hosted_fields_style']['base']['fontSize'];
        $cardPaymentSettings->UISettings->fontWeight = $oldConfig['payment']['global']['hosted_fields_style']['base']['fontWeight'];
        $cardPaymentSettings->UISettings->color = $oldConfig['payment']['global']['hosted_fields_style']['base']['color'];
        $cardPaymentSettings->UISettings->placeholderColor = $oldConfig['payment']['global']['hosted_fields_style']['base']['placeholderColor'];
        $cardPaymentSettings->UISettings->caretColor = $oldConfig['payment']['global']['hosted_fields_style']['base']['caretColor'];
        $cardPaymentSettings->UISettings->iconColor = $oldConfig['payment']['global']['hosted_fields_style']['base']['iconColor'];

        $defaultCurrencyIso = \AG\PSModuleUtils\Tools::getIsoCurrencyCodeById((int) Configuration::get('PS_CURRENCY_DEFAULT'));
        foreach ($cardPaymentSettings->paymentMethods as $key => $paymentMethod) {
            $oldCardConfig = Configuration::get(sprintf('HIPAY_PAYMENT_%s', strtoupper($paymentMethod->code)));
            if (!$oldCardConfig) {
                continue;
            }
            $oldCardConfig = (array) json_decode($oldCardConfig, true);
            $cardPaymentSettings->paymentMethods[$key]->enabled = (bool) $oldCardConfig['activated'];
            $cardPaymentSettings->paymentMethods[$key]->minAmount = (float) $oldCardConfig['minAmount'][$defaultCurrencyIso] ? $oldCardConfig['minAmount'][$defaultCurrencyIso] : 0;
            $cardPaymentSettings->paymentMethods[$key]->maxAmount = (float) $oldCardConfig['maxAmount'][$defaultCurrencyIso] ? $oldCardConfig['maxAmount'][$defaultCurrencyIso] : 0;
        }

        /** @var \HiPay\PrestaShop\Settings\Updater\CardPaymentSettingsUpdater $updater */
        $updater = $this->module->getService('hp.settings.card_payment.updater');
        try {
            $updater->updateObject($cardPaymentSettings);
        } catch (\AG\PSModuleUtils\Exception\ExceptionList $e) {
            $logger->error('Error while updating card payment settings', ['errors' => $e->getExceptionsMessages()]);
            die(json_encode(['success' => false, 'message' => $this->module->l('An error occurred. Please consult the logs.', 'AdminHiPayPaymentsAjaxController')]));
        }

        $logger->info('End of card settings migration');
        die(json_encode(['success' => true]));
    }

    /**
     * @return void
     */
    public function ajaxProcessMigrateAdvancedPaymentMethodsSettings()
    {
        /** @var \HiPay\PrestaShop\Logger\LoggerFactory $loggerFactory */
        $loggerFactory = $this->module->getService('hp.logger.factory');
        $logger = $loggerFactory->withChannel('MigrationAPM');

        $logger->info('Start migration of APM settings');
        /** @var \HiPay\PrestaShop\Settings\SettingsLoader $settingsLoader */
        $settingsLoader = $this->module->getService('hp.settings.loader');
        /** @var HiPay\PrestaShop\Settings\Settings $settings */
        $settings = $settingsLoader->load();
        $otherPMSettings = $settings->otherPMSettings;

        $defaultCurrencyIso = \AG\PSModuleUtils\Tools::getIsoCurrencyCodeById((int) Configuration::get('PS_CURRENCY_DEFAULT'));
        foreach ($otherPMSettings->paymentMethods as $key => $paymentMethod) {
            $oldCardConfig = Configuration::get(sprintf('HIPAY_PAYMENT_%s', strtoupper($paymentMethod->code)));
            if (!$oldCardConfig) {
                continue;
            }
            $oldCardConfig = (array) json_decode($oldCardConfig, true);
            $otherPMSettings->paymentMethods[$key]->enabled = (bool) $oldCardConfig['activated'];
            if (!in_array($paymentMethod->code, ['alma-3x', 'alma-4x'])) {
                $otherPMSettings->paymentMethods[$key]->minAmount = (float) $oldCardConfig['minAmount'][$defaultCurrencyIso] ? $oldCardConfig['minAmount'][$defaultCurrencyIso] : 0;
                $otherPMSettings->paymentMethods[$key]->maxAmount = (float) $oldCardConfig['maxAmount'][$defaultCurrencyIso] ? $oldCardConfig['maxAmount'][$defaultCurrencyIso] : 0;
            }
            if (\HiPay\PrestaShop\Settings\Entity\APM\Multibanco::class === get_class($otherPMSettings->paymentMethods[$key])) {
                $otherPMSettings->paymentMethods[$key]->expirationLimit = (int) $oldCardConfig['orderExpirationTime'];
            }
            if (\HiPay\PrestaShop\Settings\Entity\APM\ApplePay::class === get_class($otherPMSettings->paymentMethods[$key])) {
                $otherPMSettings->paymentMethods[$key]->merchantIdentifier = $oldCardConfig['merchantId'];
            }
            $otherPMSettings->paymentMethods[$key]->position = (int) $oldCardConfig['frontPosition'];
        }

        usort($otherPMSettings->paymentMethods, function($a, $b) {
            if ($a->position !== $b->position) {
                return $a->position - $b->position;
            }

            return strcmp($a->code, $b->code);
        });
        foreach ($otherPMSettings->paymentMethods as $k => $paymentMethod) {
            $otherPMSettings->paymentMethods[$k]->position = $k + 1;
        }

        /** @var \HiPay\PrestaShop\Settings\Updater\OtherPMSettingsUpdater $updater */
        $updater = $this->module->getService('hp.settings.other_pm.updater');
        try {
            $updater->updateObject($otherPMSettings);
        } catch (\AG\PSModuleUtils\Exception\ExceptionList $e) {
            $logger->error('Error while updating advanced payment settings', ['errors' => $e->getExceptionsMessages()]);
            die(json_encode(['success' => false, 'message' => $this->module->l('An error occurred. Please consult the logs.', 'AdminHiPayPaymentsAjaxController')]));
        }

        $logger->info('End of APM settings migration');
        die(json_encode(['success' => true]));
    }

    /**
     * @return void
     */
    public function ajaxProcessMigrateDatabase()
    {
        /** @var \HiPay\PrestaShop\Logger\LoggerFactory $loggerFactory */
        $loggerFactory = $this->module->getService('hp.logger.factory');
        $logger = $loggerFactory->withChannel('MigrationDatabase');

        $logger->info('Start migration of database');

        try {
            $logger->info('Migrate transactions');
            $this->migrateTransactions($logger);
            $logger->info('Migrate tokens');
            $this->migrateTokens($logger);
        } catch (Exception $e) {
            $logger->error($e->getMessage(), ['trace' => $e->getTraceAsString()]);
            die(json_encode(['success' => false, 'message' => $this->module->l('An error occurred. Please consult the logs.', 'AdminHiPayPaymentsAjaxController')]));
        }

        $logger->info('End of database migration');
        die(json_encode(['success' => true]));
    }

    /**
     * @param \Monolog\Logger $logger
     * @return void
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function migrateTokens(\Monolog\Logger $logger): void
    {
        $dbSubQuery = (new DbQuery())
            ->select('card_token')
            ->from('hipaypayments_customer_card');
        $dbQuery = (new DbQuery())
            ->select('cct.*')
            ->from('hipay_cc_token', 'cct')
            ->where(sprintf('cct.token NOT IN (%s)', $dbSubQuery->build()));

        $rows = (array) Db::getInstance()->executeS($dbQuery);
        if (!$rows) {
            $logger->info('No data to copy. End of database migration');

            return;
        }
        $chunks = array_chunk($rows, 100);
        foreach ($chunks as $chunk) {
            $values = [];
            foreach ($chunk as $row) {
                $values[] = [
                    'id_hipaypayments_customer_card' => null,
                    'id_customer' => (int) $row['customer_id'],
                    'payment_product' => pSQL($row['brand']),
                    'card_token' => pSQL($row['token']),
                    'card_brand' => pSQL(strtoupper($row['brand'])),
                    'card_pan' => pSQL(str_replace('*', 'x', $row['pan'])),
                    'card_expiry_month' => pSQL($row['card_expiry_month']),
                    'card_expiry_year' => pSQL($row['card_expiry_year']),
                    'card_holder' => pSQL($row['card_holder']),
                    'date_add' => date('Y-m-d H:i:s'),
                ];
            }

            if (count($values)) {
                try {
                    Db::getInstance()->insert(
                        'hipaypayments_customer_card',
                        $values
                    );
                } catch (PrestaShopDatabaseException $e) {
                    $logger->error($e->getMessage());
                    continue;
                }
            }
        }
    }

    /**
     * @param \Monolog\Logger $logger
     * @return void
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function migrateTransactions(\Monolog\Logger $logger): void
    {
        $dbSubQuery = (new DbQuery())
            ->select('id_order')
            ->from('hipaypayments_order');
        $dbQuery = (new DbQuery())
            ->select('ht.`order_id`, ht.`transaction_ref`, o.`id_cart`')
            ->from('hipay_transaction', 'ht')
            ->leftJoin('orders', 'o', 'o.`id_order` = ht.`order_id`')
            ->where(sprintf('ht.order_id NOT IN (%s)', $dbSubQuery->build()))
            ->groupBy('ht.`order_id`');

        $rows = (array) Db::getInstance()->executeS($dbQuery);
        if (!$rows) {
            $logger->info('No data to copy. End of database migration');

            return;
        }
        $chunks = array_chunk($rows, 100);

        foreach ($chunks as $chunk) {
            $values = [];
            foreach ($chunk as $row) {
                $values[] = [
                    'id_hipaypayments_order' => null,
                    'id_order' => (int) $row['order_id'],
                    'id_cart' => (int) $row['id_cart'],
                    'hipay_transaction_reference' => pSQL($row['transaction_ref']),
                    'hipay_order_id' => ' ',
                    'date_add' => date('Y-m-d H:i:s'),
                ];
            }

            if (count($values)) {
                try {
                    Db::getInstance()->insert(
                        'hipaypayments_order',
                        $values
                    );
                } catch (PrestaShopDatabaseException $e) {
                    $logger->error($e->getMessage());
                    continue;
                }
            }
        }
    }

    /**
     * @return void
     */
    public function ajaxProcessDeclineMigration()
    {
        Configuration::updateGlobalValue(\HiPay\PrestaShop\Settings\Settings::PS_CONFIG_KEY_MIGRATION_PAGE_DISPLAYED, false);
        die(json_encode(['success' => true]));
    }
}
