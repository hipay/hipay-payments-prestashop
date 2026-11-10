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
use HiPay\PrestaShop\Utils\MySqlLock;

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
        } catch (Throwable $e) {
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
        $dbQuery = (new \DbQuery())
            ->select('*')
            ->from(pSQL(NotificationProcessor::QUEUED_NOTIFICATIONS_TABLE_NAME))
            ->where('is_processed = 0')
            ->where('is_failed = 0')
            ->where(sprintf('attempts < %d', (int) NotificationProcessor::MAX_ATTEMPTS))
            ->groupBy('transaction_reference');

        $rows = (array) \Db::getInstance((bool) _PS_USE_SQL_SLAVE_)->executeS($dbQuery) ?: [];
        foreach ($rows as $row) {
            /** @var \HiPay\PrestaShop\Processor\NotificationProcessor $notificationProcessor */
            $notificationProcessor = $this->module->getService('hp.notification.processor');
            $transaction = $notificationProcessor->mapQueuedPayload((string) $row['payload']);
            if (null === $transaction) {
                $transaction = $this->findValidQueuedTransaction($notificationProcessor, (string) $row['transaction_reference']);
            }
            if (null === $transaction) {
                $discarded = $notificationProcessor->discardInvalidQueuedNotifications((string) $row['transaction_reference']);
                $logger->error(sprintf('Transaction %s - %d queued notification(s) with invalid payload marked as failed', $row['transaction_reference'], $discarded));
                continue;
            }
            $cartReference = $transaction->getOrder()->getId();
            $pos = strpos($cartReference, '-');
            $cartId = ($pos !== false) ? substr($cartReference, 0, $pos) : false;
            if (!$cartId) {
                continue;
            }
            $cart = new Cart((int) $cartId);

            try {
                $lockKey = sprintf('hipaypayments_lock_%s', $transaction->getTransactionReference());
                $lock = new MySqlLock();
                if ($lock->acquire($lockKey)) {
                    $logger->debug(sprintf('Process transaction reference %s', $transaction->getTransactionReference()));
                    $notificationProcessor->cart = $cart;
                    $notificationProcessor->logger = $logger;
                    $notificationProcessor->processQueuedNotifications($transaction->getTransactionReference());
                } else {
                    continue;
                }
            } catch (Throwable $e) {
                $logger->error($e->getMessage());
                continue;
            }
        }
    }

    /**
     * @param NotificationProcessor $notificationProcessor
     * @param string                $transactionReference
     * @return \HiPay\Fullservice\Gateway\Model\Transaction|null
     * @throws PrestaShopDatabaseException
     */
    private function findValidQueuedTransaction(NotificationProcessor $notificationProcessor, string $transactionReference)
    {
        $dbQuery = (new \DbQuery())
            ->select('payload')
            ->from(pSQL(NotificationProcessor::QUEUED_NOTIFICATIONS_TABLE_NAME))
            ->where(sprintf('transaction_reference = "%s"', pSQL($transactionReference)))
            ->where('is_processed = 0')
            ->where('is_failed = 0')
            ->where(sprintf('attempts < %d', (int) NotificationProcessor::MAX_ATTEMPTS))
            ->orderBy('received_at DESC');

        $rows = (array) \Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($dbQuery) ?: [];
        foreach ($rows as $row) {
            $transaction = $notificationProcessor->mapQueuedPayload((string) $row['payload']);
            if (null !== $transaction) {
                return $transaction;
            }
        }

        return null;
    }
}
