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
     * @return void
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \Exception
     */
    public function process(TransactionPresented $transactionPresented)
    {
        if ($transactionPresented->validateOrder) {
            $this->module->validateOrder(
                $transactionPresented->validation['idCart'],
                $transactionPresented->validation['idOrderState'],
                $transactionPresented->validation['amount'],
                $transactionPresented->validation['paymentMethod'],
                null,
                [],
                null,
                false,
                $transactionPresented->validation['secureKey'],
                new \Shop($transactionPresented->validation['idShop'])
            );
            $order = Tools::getOrderByCartId((int) $transactionPresented->validation['idCart']);
            if (!$order->id) {
                throw new \Exception('Order not found. Native validation may have failed.');
            }
        } elseif ($transactionPresented->updateStatus) {
            $order = new \Order((int) $transactionPresented->orderId);
            if (!$order->getHistory($order->id_lang, (int) $transactionPresented->newStatus)) {
                $orderHistory = new \OrderHistory();
                $orderHistory->id_order = (int) $order->id;
                $orderHistory->changeIdOrderState((int) $transactionPresented->newStatus, (int) $order->id);
                $orderHistory->addWithemail();
            }
        } else {
            return;
        }

        if ($transactionPresented->validateOrder || $transactionPresented->saveMotoTransaction) {
            $hiPayOrder = new \HiPayPaymentsOrder();
            $hiPayOrder->id_order = (int) $order->id;
            $hiPayOrder->id_cart = (int) $order->id_cart;
            $hiPayOrder->hipay_transaction_reference = pSQL($transactionPresented->transaction['transactionReference']);
            $hiPayOrder->hipay_order_id = pSQL((string) $transactionPresented->transaction['hiPayOrderId']);
            $hiPayOrder->save();
        }
    }
}
