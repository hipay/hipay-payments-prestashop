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

use HiPay\PrestaShop\Utils\AmountOfMoney;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class AdminHiPayPaymentsAjaxTransactionsController
 */
class AdminHiPayPaymentsAjaxTransactionsController extends ModuleAdminController
{
    /** @var HiPayPayments $module */
    public $module;

    /**
     * @return bool
     * @throws Exception
     */
    public function checkTransactionPermission(): bool
    {
        $employee = Context::getContext()->employee;
        $tab = new Tab((int) Tab::getIdFromClassName('AdminHiPayPaymentsAjaxTransactions'));
        if (!Validate::isLoadedObject($tab)) {
            return false;
        }
        /** @var bool|mixed[] $access */
        $access = Profile::getProfileAccess((int) $employee->id_profile, $tab->id);

        return false !== $access && is_array($access) && $access['view'] == 1;
    }

    /**
     * @return void
     */
    public function displayAjaxProcessOperation()
    {
        $data = Tools::getValue('data');
        switch ($data['operationType']) {
            case 'partial-capture':
            case 'full-capture':
                $this->processCapture($data);
                break;
            case 'refund':
            default:
                $this->processRefund($data);
        }
    }

    /**
     * @param mixed[] $postedData
     * @return void
     */
    public function processCapture(array $postedData)
    {
        $returnedData = [];
        /** @var \HiPay\PrestaShop\Transaction\TransactionDetailsService $transactionDetailsService */
        $transactionDetailsService = $this->module->getService('hp.transaction_details.service');
        /** @var \HiPay\PrestaShop\Api\PrestaShopSDK $sdk */
        $sdk = $this->module->getService('hp.sdk.gateway');
        $hipayPaymentsOrder = new HiPayPaymentsOrder((int) $postedData['idHiPayOrder']);
        try {
            if (false === $this->checkTransactionPermission()) {
                throw new Exception($this->module->l('Permission denied. Please verify your profile permissions', 'AdminHiPayPaymentsAjaxTransactionsController'));
            }
            $order = new Order((int) $hipayPaymentsOrder->id_order);
            $captureOperation = $sdk
                ->init($order->id_shop, $order->id_shop_group)
                ->server()
                ->requestMaintenanceOperation(
                    'capture',
                    $hipayPaymentsOrder->hipay_transaction_reference,
                    $postedData['amount']
                );

            switch ($captureOperation->getStatus()) {
                case \HiPay\PrestaShop\Presenter\TransactionPresenter::STATUS_PARTIALLY_CAPTURED:
                    $message = $this->module->l('Amount partially captured successfully.', 'AdminHiPayPaymentsAjaxTransactionsController');
                    $data = ['operationSuccess' => true];
                    break;
                case \HiPay\PrestaShop\Presenter\TransactionPresenter::STATUS_CAPTURED:
                    $message = $this->module->l('Amount captured successfully.', 'AdminHiPayPaymentsAjaxTransactionsController');
                    $data = ['operationSuccess' => true];
                    break;
                case \HiPay\PrestaShop\Presenter\TransactionPresenter::STATUS_CAPTURE_REQUEST:
                    $message = $this->module->l('Capture requested successfully.', 'AdminHiPayPaymentsAjaxTransactionsController');
                    $data = ['operationSuccess' => true];
                    break;
                case \HiPay\PrestaShop\Presenter\TransactionPresenter::STATUS_CAPTURE_DECLINED:
                default:
                    $message = $this->module->l('Amount could not be captured.', 'AdminHiPayPaymentsAjaxTransactionsController');
                    $data = ['operationSuccess' => false];
                    break;
            }
            $data['operationMessage'] = $message;

            $returnedData = [
                'orderLink' => $this->context->link->getAdminLink('AdminOrders', true, [], ['id_order' => $order->id, 'vieworder' => 1, 'capture_success' => 1]),
            ];
        } catch (Exception $e) {
            $data = [
                'operationSuccess' => false,
                'operationMessage' => $e->getMessage(),
            ];
        }
        try {
            $data = array_merge(
                $data,
                $transactionDetailsService->retrieveTransactionDetails($hipayPaymentsOrder)
            );
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
        ] + $returnedData + ['operation' => isset($captureOperation) ? $captureOperation->toArray() : []]));
    }

    /**
     * @param mixed[] $postedData
     * @return void
     */
    public function processRefund(array $postedData)
    {
        $returnedData = [];
        /** @var \HiPay\PrestaShop\Transaction\TransactionDetailsService $transactionDetailsService */
        $transactionDetailsService = $this->module->getService('hp.transaction_details.service');
        /** @var \HiPay\PrestaShop\Api\PrestaShopSDK $sdk */
        $sdk = $this->module->getService('hp.sdk.gateway');
        $hipayPaymentsOrder = new HiPayPaymentsOrder((int) $postedData['idHiPayOrder']);
        try {
            if (false === $this->checkTransactionPermission()) {
                throw new Exception($this->module->l('Permission denied. Please verify your profile permissions', 'AdminHiPayPaymentsAjaxTransactionsController'));
            }
            $order = new Order((int) $hipayPaymentsOrder->id_order);
            $refundOperation = $sdk
                ->init($order->id_shop, $order->id_shop_group)
                ->server()
                ->requestMaintenanceOperation(
                    'refund',
                    $hipayPaymentsOrder->hipay_transaction_reference,
                    $postedData['amount']
                );

            switch ($refundOperation->getStatus()) {
                case \HiPay\PrestaShop\Presenter\TransactionPresenter::STATUS_PARTIALLY_REFUNDED:
                    $message = $this->module->l('Amount partially refunded successfully.', 'AdminHiPayPaymentsAjaxTransactionsController');
                    $data = ['operationSuccess' => true];
                    break;
                case \HiPay\PrestaShop\Presenter\TransactionPresenter::STATUS_REFUNDED:
                    $message = $this->module->l('Amount refunded successfully.', 'AdminHiPayPaymentsAjaxTransactionsController');
                    $data = ['operationSuccess' => true];
                    break;
                case \HiPay\PrestaShop\Presenter\TransactionPresenter::STATUS_REFUND_REQUESTED:
                    $message = $this->module->l('Refund requested successfully.', 'AdminHiPayPaymentsAjaxTransactionsController');
                    $data = ['operationSuccess' => true];
                    break;
                case \HiPay\PrestaShop\Presenter\TransactionPresenter::STATUS_REFUND_DECLINED:
                default:
                    $message = $this->module->l('Amount could not be refunded.', 'AdminHiPayPaymentsAjaxTransactionsController');
                    $data = ['operationSuccess' => false];
                    break;
            }
            $data['operationMessage'] = $message;

            $returnedData = [
                'orderLink' => $this->context->link->getAdminLink('AdminOrders', true, [], ['id_order' => $order->id, 'vieworder' => 1, 'refund_success' => 1]),
            ];
        } catch (Exception $e) {
            $data = [
                'operationSuccess' => false,
                'operationMessage' => $e->getMessage(),
            ];
        }
        try {
            $data = array_merge(
                $data,
                $transactionDetailsService->retrieveTransactionDetails($hipayPaymentsOrder)
            );
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
            ] + $returnedData + ['operation' => isset($refundOperation) ? $refundOperation->toArray() : []]));
    }
}
