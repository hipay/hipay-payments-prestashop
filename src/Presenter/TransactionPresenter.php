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

namespace HiPay\PrestaShop\Presenter;

use AG\PSModuleUtils\Presenter\PresenterInterface;
use AG\PSModuleUtils\Utils\AmountOfMoney;
use HiPay\Fullservice\Gateway\Model\Transaction;
use HiPay\PrestaShop\Settings\Entity\MainSettings;
use HiPay\PrestaShop\Settings\Settings;
use HiPay\PrestaShop\Settings\SettingsLoader;
use HiPay\PrestaShop\Utils\Tools;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class TransactionPresenter
 */
class TransactionPresenter implements PresenterInterface
{
    const STATUS_AUTHORIZED = 116;
    const STATUS_AUTHORIZATION_REQUESTED = 142;
    const STATUS_CAPTURE_REQUEST = 117;
    const STATUS_CAPTURED = 118;
    const STATUS_PARTIALLY_CAPTURED = 119;
    const STATUS_CAPTURE_DECLINED = 173;
    const STATUS_REFUND_REQUESTED = 124;
    const STATUS_REFUNDED = 125;
    const STATUS_PARTIALLY_REFUNDED = 126;
    const STATUS_REFUND_DECLINED = 165;
    const STATUS_CHARGED_BACK_DEPRECATED = 129;
    const STATUS_CHARGED_BACK = 181;
    const STATUS_PARTIALLY_CHARGED_BACK = 180;
    const STATUS_EXPIRED = 114;
    const STATE_FORWARDING = 'forwarding';
    const MULTIBANCO_PAYMENT_PRODUCT_CODE = 'multibanco';
    const MOONEY_PAYMENT_PRODUCT_CODE = 'sisal';

    /** @var \HiPayPayments */
    private $module;

    /** @var SettingsLoader */
    private $settingsLoader;

    /** @var Settings */
    private $settings;

    /** @var TransactionPresented */
    private $dataPresented;

    /**
     * TransactionPresenter Constructor.
     *
     * @param \HiPayPayments $module
     * @param SettingsLoader $settingsLoader
     */
    public function __construct(\HiPayPayments $module, SettingsLoader $settingsLoader)
    {
        $this->module = $module;
        $this->settingsLoader = $settingsLoader;
    }

    /**
     * @param Transaction $object
     * @param \Cart|null  $cart
     * @return TransactionPresented
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function present($object, \Cart $cart = null): TransactionPresented
    {
        $this->settings = $this->settingsLoader->withContext($cart->id_shop, $cart->id_shop_group, true);
        $this->dataPresented = new TransactionPresented();

        $order = Tools::getOrderByCartId($cart->id);
        if (\Validate::isLoadedObject($order)) {
            return $this->presentExistingOrder($order, $object);
        } else {
            return $this->presentNewOrder($cart, $object);
        }
    }

    /**
     * @param Transaction $transaction
     * @return false|int
     */
    public function getPSStatusIdFromHiPayStatusId(Transaction $transaction)
    {
        $statusId = $transaction->getStatus();
        switch ($statusId) {
            case self::STATUS_AUTHORIZED:
                return $this->settings->mainSettings->captureMode === MainSettings::CAPTURE_MODE_AUTO ? $this->settings->pendingAuthStatusId : $this->settings->pendingCaptureStatusId;
            case self::STATUS_CAPTURED:
                $transactionCurrencyCode = $transaction->getCurrency();
                $authorizedAmount = AmountOfMoney::fromStandardUnit($transaction->getAuthorizedAmount(), $transactionCurrencyCode);
                $capturedAmount = AmountOfMoney::fromStandardUnit($transaction->getCapturedAmount(), $transactionCurrencyCode);

                if (0 === $authorizedAmount->compare($capturedAmount)) {
                    $idOrderState = (int) \Configuration::getGlobalValue('PS_OS_PAYMENT');
                } elseif ($capturedAmount->getAmount()) {
                    $idOrderState = $this->settings->partiallyCapturedStatusId;
                } else {
                    return false;
                }

                return $idOrderState;
            case self::STATUS_CHARGED_BACK_DEPRECATED:
            case self::STATUS_CHARGED_BACK:
            case self::STATUS_PARTIALLY_CHARGED_BACK:
                return $this->settings->chargebackStatusId;
            case self::STATUS_AUTHORIZATION_REQUESTED:
                return $transaction->getState() !== self::STATE_FORWARDING || self::MULTIBANCO_PAYMENT_PRODUCT_CODE === $transaction->getPaymentProduct() || self::MOONEY_PAYMENT_PRODUCT_CODE === $transaction->getPaymentProduct() ? $this->settings->pendingAuthStatusId : false;
            case self::STATUS_PARTIALLY_REFUNDED:
                return $this->settings->partiallyRefundedStatusId;
            case self::STATUS_REFUNDED:
                return (int) \Configuration::getGlobalValue('PS_OS_REFUND');
            case self::STATUS_PARTIALLY_CAPTURED:
                return $this->settings->partiallyCapturedStatusId;
            case self::STATUS_EXPIRED:
                return (int) \Configuration::getGlobalValue('PS_OS_CANCELED');
            default:
                return false;
        }
    }

    /**
     * @param \Cart       $cart
     * @param Transaction $transaction
     * @return TransactionPresented
     */
    public function presentNewOrder(\Cart $cart, Transaction $transaction): TransactionPresented
    {
        $idOrderState = $this->getPSStatusIdFromHiPayStatusId($transaction);
        if (false === $idOrderState) {
            return $this->dataPresented;
        }
        $paymentMethod = $this->settings->cardPaymentSettings->findByCode($transaction->getPaymentProduct());
        $paymentMethodName = 'N/A';
        if (false === $paymentMethod) {
            $paymentMethod = $this->settings->otherPMSettings->findByCode($transaction->getPaymentProduct());
            if (false !== $paymentMethod) {
                $paymentMethodName = $paymentMethod->name;
            }
        } else {
            $paymentMethodName = $paymentMethod->name;
        }

        $amountOfMoney = AmountOfMoney::fromStandardUnit($transaction->getOrder()->getAmount(), $transaction->getOrder()->getCurrency());
        $this->dataPresented->validateOrder = true;
        $this->dataPresented->validation['idCart'] = $cart->id;
        $this->dataPresented->validation['idOrderState'] = $idOrderState;
        $this->dataPresented->validation['idShop'] = $cart->id_shop;
        $this->dataPresented->validation['transactionReference'] = $transaction->getTransactionReference();
        $this->dataPresented->validation['amount'] = $amountOfMoney->getAmount();
        $this->dataPresented->validation['paymentMethod'] = sprintf('%s [%s]', $this->module->displayName, $paymentMethodName);
        $this->dataPresented->validation['secureKey'] = $cart->secure_key;
        $this->dataPresented->transaction['transactionReference'] = $transaction->getTransactionReference();
        $this->dataPresented->transaction['hiPayOrderId'] = $transaction->getOrder()->getId();
        $this->dataPresented->transaction['idCart'] = $cart->id;

        return $this->dataPresented;
    }

    /**
     * @param \Order      $order
     * @param Transaction $transaction
     * @return TransactionPresented
     */
    public function presentExistingOrder(\Order $order, Transaction $transaction): TransactionPresented
    {
        if ($order->module !== $this->module->name) {
            return $this->dataPresented;
        }
        $idOrderState = $this->getPSStatusIdFromHiPayStatusId($transaction);
        if (false === $idOrderState) {
            return $this->dataPresented;
        }
        $this->dataPresented->updateStatus = true;
        $this->dataPresented->newStatus = (int) $idOrderState;
        $this->dataPresented->orderId = (int) $order->id;
        $this->dataPresented->transaction['transactionReference'] = $transaction->getTransactionReference();
        $this->dataPresented->transaction['hiPayOrderId'] = $transaction->getOrder()->getId();
        $this->dataPresented->transaction['idCart'] = $order->id_cart;
        try {
            $isMotoOrder = \Validate::isLoadedObject(\HiPayPaymentsMotoOrder::getHiPayMotoOrderByPsOrderId($order->id));
            $needTransactionSave = !\Validate::isLoadedObject(\HiPayPaymentsOrder::getHiPayOrderByPsOrderId($order->id));
            $this->dataPresented->saveMotoTransaction = $isMotoOrder && $needTransactionSave;
        } catch (\Exception $e) {
            return $this->dataPresented;
        }


        return $this->dataPresented;
    }
}
