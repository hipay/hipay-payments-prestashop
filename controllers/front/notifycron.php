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

use HiPay\PrestaShop\Processor\NotificationProcessor;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class HiPayPaymentsNotifyCronModuleFrontController
 */
class HiPayPaymentsNotifyCronModuleFrontController extends ModuleFrontController
{
    /** @var HiPayPayments */
    public $module;

    /**
     * HiPayPaymentsNotifyCronModuleFrontController Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->ajax = true;
    }

    /**
     * @return void
     */
    public function displayAjaxProcessQueue()
    {
        /** @var \HiPay\PrestaShop\Logger\LoggerFactory $loggerFactory */
        $loggerFactory = $this->module->getService('hp.logger.factory');
        $logger = $loggerFactory->withChannel('CronNotify');
        $logger->info('Start of Cron notify');
        try {
            $this->processQueue($logger);
        } catch (Exception $e) {
            $logger->error($e->getMessage());
        }
        $logger->info('End of Cron notify');
    }

    /**
     * @param \Monolog\Logger $logger
     * @return void
     * @throws PrestaShopDatabaseException
     */
    private function processQueue(\Monolog\Logger $logger)
    {
        /** @var \Symfony\Component\Lock\LockFactory $lockFactory */
        $lockFactory = $this->module->getService('hp.notification.lock_factory');
        $dbQuery = (new \DbQuery())
            ->select('*')
            ->from(pSQL(NotificationProcessor::QUEUED_NOTIFICATIONS_TABLE_NAME))
            ->where('is_processed = 0')
            ->where('is_failed = 0')
            ->where(sprintf('attempts < %d', (int) NotificationProcessor::MAX_ATTEMPTS))
            ->groupBy('transaction_reference');

        $rows = (array) \Db::getInstance((bool) _PS_USE_SQL_SLAVE_)->executeS($dbQuery) ?: [];
        foreach ($rows as $row) {
            $transactionMapper = new \HiPay\Fullservice\Gateway\Mapper\TransactionMapper(json_decode($row['payload'], true));
            /** @var \HiPay\Fullservice\Gateway\Model\Transaction $transaction */
            $transaction = $transactionMapper->getModelObjectMapped();
            $cartReference = $transaction->getOrder()->getId();
            $pos = strpos($cartReference, '-');
            $cartId = ($pos !== false) ? substr($cartReference, 0, $pos) : false;
            if (!$cartId) {
                continue;
            }
            $cart = new Cart((int) $cartId);

            /** @var \HiPay\PrestaShop\Processor\NotificationProcessor $notificationProcessor */
            $notificationProcessor = $this->module->getService('hp.notification.processor');
            try {
                $lockKey = sprintf('hipaypayments_lock_%s', $transaction->getTransactionReference());
                $lock = $lockFactory->createLock($lockKey, 30);
                if ($lock->acquire()) {
                    $logger->debug(sprintf('Process transaction reference %s', $transaction->getTransactionReference()));
                    $notificationProcessor->cart = $cart;
                    $notificationProcessor->processQueuedNotifications($transaction->getTransactionReference());
                } else {
                    continue;
                }
            } catch (Exception $e) {
                $logger->error($e->getMessage());
                continue;
            }
        }
    }
}
