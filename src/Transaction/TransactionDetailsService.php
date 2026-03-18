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

namespace HiPay\PrestaShop\Transaction;

use HiPay\PrestaShop\Api\PrestaShopSDK;
use HiPay\PrestaShop\Presenter\AdminTransactionPresenter;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class TransactionDetailsService
 */
class TransactionDetailsService
{
    /** @var \HiPayPayments */
    private $module;

    /** @var PrestaShopSDK */
    private $sdk;

    /** @var AdminTransactionPresenter */
    private $adminTransactionPresenter;

    /**
     * TransactionDetailsService Constructor.
     *
     * @param \HiPayPayments            $module
     * @param PrestaShopSDK             $sdk
     * @param AdminTransactionPresenter $adminTransactionPresenter
     */
    public function __construct(
        \HiPayPayments $module,
        PrestaShopSDK $sdk,
        AdminTransactionPresenter $adminTransactionPresenter
    ) {
        $this->module = $module;
        $this->sdk = $sdk;
        $this->adminTransactionPresenter = $adminTransactionPresenter;
    }

    /**
     * @param \HiPayPaymentsOrder $hipayPaymentsOrder
     * @return mixed[]
     * @throws \Exception
     */
    public function retrieveTransactionDetails(\HiPayPaymentsOrder $hipayPaymentsOrder): array
    {
        try {
            if (!\Validate::isLoadedObject($hipayPaymentsOrder)) {
                throw new \Exception('Cannot retrieve transaction details');
            }
            $order = new \Order((int) $hipayPaymentsOrder->id_order);
            $transaction = $this->sdk
                ->init($order->id_shop, $order->id_shop_group)
                ->server()
                ->requestTransactionInformation($hipayPaymentsOrder->hipay_transaction_reference);

            if (null === $transaction) {
                throw new \Exception();
            }

            return $this->adminTransactionPresenter->present($transaction);
        } catch (\Exception $e) {
            throw new \Exception($this->module->l('Cannot retrieve transaction details', 'TransactionDetailsService'));
        }
    }
}
