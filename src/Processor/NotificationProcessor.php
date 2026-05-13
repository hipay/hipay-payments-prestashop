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

namespace HiPay\PrestaShop\Processor;

use HiPay\Fullservice\Gateway\Model\Transaction;
use HiPay\PrestaShop\Presenter\TransactionPresenter;
use HiPay\PrestaShop\Utils\MySqlLock;
use Monolog\Logger;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class NotificationProcessor
 */
class NotificationProcessor
{
    const QUEUED_NOTIFICATIONS_TABLE_NAME = 'hipaypayments_queued_notification';
    const MAX_ATTEMPTS = 5;

    /** @var \Cart */
    public $cart;

    /** @var Logger */
    public $logger;

    /** @var TransactionPresenter */
    private $transactionPresenter;

    /** @var TransactionProcessor */
    private $transactionProcessor;

    /**
     * NotificationProcessor Constructor.
     *
     * @param TransactionPresenter $transactionPresenter
     * @param TransactionProcessor $transactionProcessor
     */
    public function __construct(
        TransactionPresenter $transactionPresenter,
        TransactionProcessor $transactionProcessor
    ) {
        $this->transactionPresenter = $transactionPresenter;
        $this->transactionProcessor = $transactionProcessor;
    }

    /**
     * @param Transaction $transaction
     * @param \Cart       $cart
     * @param Logger      $logger
     * @return void
     * @throws \PrestaShopDatabaseException
     */
    public function process(Transaction $transaction, \Cart $cart, Logger $logger)
    {
        $this->cart = $cart;
        $this->logger = $logger;
        $lockKey = sprintf('hipaypayments_lock_%s', $transaction->getTransactionReference());
        $lock = new MySqlLock();

        if ($lock->acquire($lockKey)) {
            $this->logger->debug(sprintf('Transaction %s - Lock acquired', $transaction->getTransactionReference()));
            try {
                $this->processNotification($transaction, $cart);
                $this->logger->debug(sprintf('Transaction %s - Process queue', $transaction->getTransactionReference()));
                $this->processQueuedNotifications($transaction->getTransactionReference());
                $this->logger->debug(sprintf('Transaction %s - End of process queue', $transaction->getTransactionReference()));
            } catch (\Exception $e) {
                $this->logger->debug(sprintf('Transaction %s - Exception, store in queue', $transaction->getTransactionReference()));
                $this->storeNotificationInQueue($transaction, true);
                throw $e;
            } finally {
                $this->logger->debug(sprintf('Transaction %s - Lock released', $transaction->getTransactionReference()));
                $lock->release();
            }
        } else {
            $this->logger->debug(sprintf('Transaction %s - Cannot acquire lock', $transaction->getTransactionReference()));
            $this->storeNotificationInQueue($transaction);
        }
    }

    /**
     * @param Transaction $transaction
     * @param \Cart       $cart
     * @return void
     * @throws \Exception
     */
    private function processNotification(Transaction $transaction, \Cart $cart)
    {
        \Cache::clean('Cart::orderExists_'.(string) $cart->id);
        $dataPresented = $this->transactionPresenter->present($transaction, $cart, $this->logger);
        $action = $dataPresented->validateOrder ? 'order validation' : ($dataPresented->updateStatus ? 'status update' : 'no action');
        $this->logger->debug(sprintf('Transaction %s - Order presented (%s)', $transaction->getTransactionReference(), $action), ['data' => json_decode((string) json_encode($dataPresented), true)]);
        $this->transactionProcessor->process($dataPresented, $this->logger);
    }

    /**
     * @param string $transactionReference
     * @return mixed[]
     * @throws \PrestaShopDatabaseException
     */
    private function getQueuedNotifications(string $transactionReference): array
    {
        $dbQuery = (new \DbQuery())
            ->select('*')
            ->from(pSQL(self::QUEUED_NOTIFICATIONS_TABLE_NAME))
            ->where(sprintf('transaction_reference = "%s"', pSQL($transactionReference)))
            ->where('is_processed = 0')
            ->where('is_failed = 0')
            ->where(sprintf('attempts < %d', (int) self::MAX_ATTEMPTS));

        return (array) \Db::getInstance()->executeS($dbQuery, true, false) ?: [];
    }

    /**
     * @param mixed[] $queuedNotification
     * @return int
     */
    private function incrementAttempts(array $queuedNotification): int
    {
        $newAttempts = (int) $queuedNotification['attempts'] + 1;
        \Db::getInstance()->update(
            pSQL(self::QUEUED_NOTIFICATIONS_TABLE_NAME),
            [
                'attempts' => (int) $newAttempts,
            ],
            sprintf('id = %d', (int) $queuedNotification['id'])
        );

        return $newAttempts;
    }

    /**
     * @param mixed[] $queuedNotification
     * @return void
     */
    private function markAsFailed(array $queuedNotification)
    {
        \Db::getInstance()->update(
            pSQL(self::QUEUED_NOTIFICATIONS_TABLE_NAME),
            [
                'is_failed' => 1,
                'is_processed' => 1,
            ],
            sprintf('id = %d', (int) $queuedNotification['id'])
        );
    }

    /**
     * @param mixed[] $queuedNotification
     * @return void
     */
    private function markAsProcessed(array $queuedNotification)
    {
        \Db::getInstance()->update(
            pSQL(self::QUEUED_NOTIFICATIONS_TABLE_NAME),
            [
                'is_processed' => 1,
                'processed_at' => (new \DateTime())->format('Y-m-d H:i:s'),
            ],
            sprintf('id = %d', (int) $queuedNotification['id'])
        );
    }

    /**
     * @param Transaction $transaction
     * @param bool        $error
     * @return void
     * @throws \PrestaShopDatabaseException
     */
    private function storeNotificationInQueue(Transaction $transaction, bool $error = false)
    {
        $payload = $transaction->toJson();
        \Db::getInstance()->insert(
            pSQL(self::QUEUED_NOTIFICATIONS_TABLE_NAME),
            [
                'transaction_reference' => pSQL($transaction->getTransactionReference()),
                'status' => pSQL((string) $transaction->getStatus()),
                'payload' => pSQL((string) $payload),
                'received_at' => (new \DateTime())->format('Y-m-d H:i:s'),
                'processed_at' => true === $error ? (new \DateTime())->format('Y-m-d H:i:s') : null,
                'attempts' => true === $error ? 1 : 0,
                'is_processed' => 0,
                'is_failed' => 0,
            ]
        );
    }

    /**
     * @param string $transactionReference
     * @return void
     * @throws \PrestaShopDatabaseException
     */
    public function processQueuedNotifications(string $transactionReference)
    {
        do {
            $queuedNotifications = $this->getQueuedNotifications($transactionReference);
            if (empty($queuedNotifications)) {
                break;
            }
            usort($queuedNotifications, function($a, $b) {
                return $a['received_at'] <=> $b['received_at'];
            });
            foreach ($queuedNotifications as $queuedNotification) {
                $payload = json_decode($queuedNotification['payload'], true);
                $transactionMapper = new \HiPay\Fullservice\Gateway\Mapper\TransactionMapper($payload);
                /** @var \HiPay\Fullservice\Gateway\Model\Transaction $transaction */
                $transaction = $transactionMapper->getModelObjectMapped();
                try {
                    $this->processNotification($transaction, $this->cart);
                    $this->markAsProcessed($queuedNotification);
                } catch (\Exception $e) {
                    $this->logger->error(sprintf('Transaction %s - %s', $transaction->getTransactionReference(), $e->getMessage()));
                    $attempt = $this->incrementAttempts($queuedNotification);
                    if ($attempt >= self::MAX_ATTEMPTS) {
                        $this->markAsFailed($queuedNotification);
                    }
                    continue;
                }
            }
        } while (true);
    }
}
