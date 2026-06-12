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

use HiPay\PrestaShop\Presenter\TransactionPresented;
use HiPay\PrestaShop\Utils\Tools;
use Monolog\Logger;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class TransactionProcessor
 */
class TransactionProcessor
{
    /** @var \HiPayPayments */
    private $module;

    /**
     * TransactionProcessor Constructor.
     *
     * @param \HiPayPayments $module
     */
    public function __construct(\HiPayPayments $module)
    {
        $this->module = $module;
    }

    /**
     * @param TransactionPresented $transactionPresented
     * @param Logger               $logger
     * @return void
     * @throws \PrestaShopDatabaseException
     * @throws \Exception
     */
    public function process(TransactionPresented $transactionPresented, Logger $logger)
    {
        if ($transactionPresented->validateOrder) {
            $logger->debug(sprintf('Transaction %s - begin Process::validateOrder', $transactionPresented->validation['transactionReference']), ['time' => date('H:i:s')]);
            $this->module->validateOrder(
                $transactionPresented->validation['idCart'],
                $transactionPresented->validation['idOrderState'],
                $transactionPresented->validation['amount'],
                $transactionPresented->validation['paymentMethod'],
                null,
                [
                    'transaction_id' => $transactionPresented->validation['transactionReference']
                ],
                null,
                false,
                $transactionPresented->validation['secureKey'],
                new \Shop($transactionPresented->validation['idShop'])
            );
            $logger->debug(sprintf('Transaction %s - end of Process::validateOrder', $transactionPresented->validation['transactionReference']), ['time' => date('H:i:s')]);
            $order = Tools::getOrderByCartId((int) $transactionPresented->validation['idCart']);
            if (!$order->id) {
                throw new \Exception('Order not found. Native validation may have failed.');
            }
            $logger->debug(sprintf('Transaction %s - Tools::getOrderByCartId (after validateOrder)', $transactionPresented->validation['transactionReference']), ['data' => ['cartId' => $transactionPresented->validation['idCart'], 'orderId' => $order->id, 'module' => $order->module]]);
            if (true === $transactionPresented->token['saveToken']) {
                $logger->debug(sprintf('Transaction %s - Saving CC Token', $transactionPresented->validation['transactionReference']));
                $id = \HiPayPaymentsCustomerCard::getCustomerCardIdByPan((string) $transactionPresented->token['pan'] ?: '', (int) $transactionPresented->token['idCustomer']);
                $storedCard = new \HiPayPaymentsCustomerCard((int) $id);
                $storedCard->id_customer = (int) $transactionPresented->token['idCustomer'] ?? 0;
                $storedCard->card_token = pSQL($transactionPresented->token['token'] ?? '');
                $storedCard->card_brand = pSQL($transactionPresented->token['brand'] ?? '');
                $storedCard->payment_product = pSQL($transactionPresented->token['product'] ?? '');
                $storedCard->card_pan = pSQL($transactionPresented->token['pan'] ?? '');
                $storedCard->card_expiry_month = pSQL($transactionPresented->token['expiryMonth'] ?? '');
                $storedCard->card_expiry_year = pSQL($transactionPresented->token['expiryYear'] ?? '');
                $storedCard->card_holder = pSQL($transactionPresented->token['cardHolder'] ?? '');
                try {
                    $storedCard->save();
                    $logger->debug(sprintf('Transaction %s - CC Token saved successfully', $transactionPresented->validation['transactionReference']));
                } catch (\PrestaShopException $e) {
                    $logger->error($e->getMessage());
                }
            }
        } elseif ($transactionPresented->updateStatus) {
            $logger->debug(sprintf('Transaction %s - begin Process::updateOrderStatus', $transactionPresented->transaction['transactionReference']), ['time' => date('H:i:s'), 'needOOSChange' => $transactionPresented->needOOSChange]);
            $order = new \Order((int) $transactionPresented->orderId);
            $orderHistory = new \OrderHistory();
            $orderHistory->id_order = (int) $order->id;
            $orderHistory->changeIdOrderState((int) $transactionPresented->newStatus, (int) $order->id, true);
            $orderHistory->addWithemail();
            if ($transactionPresented->needOOSChange) {
                $oosHistory = new \OrderHistory();
                $oosHistory->id_order = (int) $order->id;
                $oosHistory->changeIdOrderState((int) $transactionPresented->oosStatus, (int) $order->id, true);
                $oosHistory->addWithemail();
                $logger->debug(sprintf('Transaction %s - re-applied backorder state %d after status update', $transactionPresented->transaction['transactionReference'], $transactionPresented->oosStatus));
            }
            $logger->debug(sprintf('Transaction %s - end of Process::updateOrderStatus', $transactionPresented->transaction['transactionReference']), ['time' => date('H:i:s')]);
        } elseif ($transactionPresented->saveMotoTransaction) {
            $order = new \Order((int) $transactionPresented->orderId);
        } else {
            return;
        }

        if ($transactionPresented->validateOrder || $transactionPresented->saveMotoTransaction) {
            $logger->debug(sprintf('Transaction %s - begin of Saving transaction', $transactionPresented->transaction['transactionReference']), ['time' => date('H:i:s')]);
            $hiPayOrder = new \HiPayPaymentsOrder();
            $hiPayOrder->id_order = (int) $order->id;
            $hiPayOrder->id_cart = (int) $order->id_cart;
            $hiPayOrder->hipay_transaction_reference = pSQL($transactionPresented->transaction['transactionReference']);
            $hiPayOrder->hipay_order_id = pSQL((string) $transactionPresented->transaction['hiPayOrderId']);
            $hiPayOrder->save();
            $logger->debug(sprintf('Transaction %s - end of Saving transaction', $transactionPresented->transaction['transactionReference']), ['time' => date('H:i:s')]);
        }
    }
}
