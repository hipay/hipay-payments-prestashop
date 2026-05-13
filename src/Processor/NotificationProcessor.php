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
use Symfony\Component\Lock\LockFactory;

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

    /** @var LockFactory */
    private $lockFactory;

    /** @var \Cart */
    public $cart;

    /** @var TransactionPresenter */
    private $transactionPresenter;

    /** @var TransactionProcessor */
    private $transactionProcessor;

    /**
     * NotificationProcessor Constructor.
     *
     * @param LockFactory          $lockFactory
     * @param TransactionPresenter $transactionPresenter
     * @param TransactionProcessor $transactionProcessor
     */
    public function __construct(
        LockFactory $lockFactory,
        TransactionPresenter $transactionPresenter,
        TransactionProcessor $transactionProcessor
    ) {
        $this->lockFactory = $lockFactory;
        $this->transactionPresenter = $transactionPresenter;
        $this->transactionProcessor = $transactionProcessor;
    }

    /**
     * @param Transaction $transaction
     * @param \Cart       $cart
     * @return void
     * @throws \PrestaShopDatabaseException
     */
    public function process(Transaction $transaction, \Cart $cart)
    {
        $this->cart = $cart;
        $lockKey = sprintf('hipaypayments_lock_%s', $transaction->getTransactionReference());
        $lock = $this->lockFactory->createLock($lockKey, 30);

        if ($lock->acquire()) {
            try {
                $this->processNotification($transaction, $cart);
                $this->processQueuedNotifications($transaction->getTransactionReference());
            } catch (\Exception $e) {
                $this->storeNotificationInQueue($transaction, true);
                throw $e;
            } finally {
                $lock->release();
            }
        } else {
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
        $dataPresented = $this->transactionPresenter->present($transaction, $cart);
        $this->transactionProcessor->process($dataPresented);
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

        return (array) \Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($dbQuery) ?: [];
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
