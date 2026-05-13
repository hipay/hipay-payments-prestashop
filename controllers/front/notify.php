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
 * Class HiPayPaymentsNotifyModuleFrontController
 */
class HiPayPaymentsNotifyModuleFrontController extends ModuleFrontController
{
    /** @var HiPayPayments */
    public $module;

    /**
     * HiPayPaymentsNotifyModuleFrontController Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->ajax = true;
    }

    /**
     * @return void
     */
    public function postProcess()
    {
        /** @var \HiPay\PrestaShop\Logger\LoggerFactory $loggerFactory */
        $loggerFactory = $this->module->getService('hp.logger.factory');
        $logger = $loggerFactory->withChannel('Notify');

        $postData = Tools::getAllValues();
        $transactionMapper = new \HiPay\Fullservice\Gateway\Mapper\TransactionMapper($postData);
        /** @var \HiPay\Fullservice\Gateway\Model\Transaction $transaction */
        $transaction = $transactionMapper->getModelObjectMapped();
        /** @var \HiPay\PrestaShop\Settings\SettingsLoader $settingsLoader */
        $settingsLoader = $this->module->getService('hp.settings.loader');
        $cartReference = $transaction->getOrder()->getId();
        $pos = strpos($cartReference, '-');
        $cartId = ($pos !== false) ? substr($cartReference, 0, $pos) : false;
        if (!$cartId) {
            $logger->error('Cart unknown', ['data' => $transaction->toArray()]);

            return;
        }
        $cart = new Cart((int) $cartId);
        $settings = $settingsLoader->withContext($cart->id_shop, $cart->id_shop_group, true);
        $credentialsTypes = [
            \HiPay\PrestaShop\Api\Credentials::CREDENTIALS_TYPE_MAIN,
            \HiPay\PrestaShop\Api\Credentials::CREDENTIALS_TYPE_APPLE_PAY,
            \HiPay\PrestaShop\Api\Credentials::CREDENTIALS_TYPE_MOTO,
        ];
        foreach ($credentialsTypes as $credentialsType) {
            $isValidSignature = \HiPay\Fullservice\Helper\Signature::isValidHttpSignature(
                $settings->getPrivateCredentials($credentialsType)->identifiers->secret,
                $settings->getPrivateCredentials($credentialsType)->hashingAlgorithm
            );
            if (true === $isValidSignature) {
                break;
            }
        }
        $logger->debug('Notification received', ['data' => $transaction->toArray(), 'validSignature' => $isValidSignature]);
        if (false === $isValidSignature) {
            $logger->error('Signature is invalid');

            return;
        }
        /** @var \HiPay\PrestaShop\Processor\NotificationProcessor $notificationProcessor */
        $notificationProcessor = $this->module->getService('hp.notification.processor');
        try {
            $notificationProcessor->process($transaction, $cart);
        } catch (Exception $e) {
            $logger->error($e->getMessage());
        }
    }
}
