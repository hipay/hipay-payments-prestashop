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

use HiPay\PrestaShop\Settings\Entity\CardPaymentSettings;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class HiPayPaymentsPaymentModuleFrontController
 */
class HiPayPaymentsPaymentModuleFrontController extends ModuleFrontController
{
    /** @var HiPayPayments */
    public $module;

    /**
     * HiPayPaymentsPaymentModuleFrontController Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return void
     * @throws Exception
     */
    public function postProcess(): void
    {
        switch (Tools::getValue('action')) {
            case 'sendHFPayment':
                $this->sendHostedFieldsPayment();
                break;
            case 'checkBancomatPayStatus':
                $this->checkBancomatPayStatus();
                break;
            default:
                break;
        }

        parent::postProcess();
    }

    /**
     * @return void
     * @throws Exception
     */
    private function sendHostedFieldsPayment()
    {
        /** @var \HiPay\PrestaShop\Api\PrestaShopSDK $sdk */
        $sdk = $this->module->getService('hp.sdk.gateway');
        /** @var \HiPay\PrestaShop\Logger\LoggerFactory $loggerFactory */
        $loggerFactory = $this->module->getService('hp.logger.factory');
        $logger = $loggerFactory->withChannel('HostedFieldsPayment');

        $data = json_decode(Tools::getValue('hipayData'), true);
        $logger->debug('Received Hosted Fields payment', null === $data ? [] : $data);

        try {
            if (null === $data) {
                throw new Exception('Received null data');
            }
            $paymentMethodCode = $data['payment_product'] ?? '';
            $isCardPayment = isset(CardPaymentSettings::CARDS_PAYMENT_CODES[$paymentMethodCode]);
            /** @var \HiPay\PrestaShop\Builder\PaymentRequestBuilderFactory $requestBuilderFactory */
            $requestBuilderFactory = $this->module->getService('hp.payment_request.builder');
            /** @var \HiPay\PrestaShop\Builder\AbstractPaymentRequestBuilder $builder */
            $builder = $requestBuilderFactory->create($data, $this->context->cart, $isCardPayment);
            /** @var \HiPay\Fullservice\Gateway\Request\Order\OrderRequest $orderRequest */
            $orderRequest = $builder->buildRequest();

            $orderRequest->payment_product = $data['payment_product'] ?? '';
            // $paymentMethod = new \HiPay\Fullservice\Gateway\Request\PaymentMethod\CardTokenPaymentMethod();
            if (isset($data['one_click']) && $data['one_click']) {
                if (!isset($data['multi_use']) || !$data['multi_use']) {
                    if (false === HiPayPaymentsCustomerCard::checkCustomerToken($this->context->customer->id, $data['token'] ?? '')) {
                        throw new Exception(sprintf('Customer token (%s) not found', $data['token'] ?? ''));
                    }
                }
            }
            if (isset($data['one_click']) && $data['one_click']) {
                $orderRequest->one_click = 1;
            }

            $transaction = $sdk
                ->init()
                ->server()
                ->requestNewOrder($orderRequest);
            if ($transaction->getState() === 'forwarding') {
                // Cas challenge 3DS
                $returnedData = [
                    'success' => true,
                    'redirectUrl' => $transaction->getForwardUrl()
                ];
            } else {
                $redirectParams = [
                    'orderid' => $transaction->getOrder()->getId(),
                    'reference' => $transaction->getTransactionReference(),
                    'action' => 'redirectFromPayment',
                    'returnType' => $transaction->getState(),
                    'paymentProduct' => $paymentMethodCode,
                ];
                $returnedData = [
                    'success' => true,
                    'redirectUrl' => $this->context->link->getModuleLink((string) $this->module->name, 'redirect', $redirectParams)
                ];
            }
        } catch (Exception $e) {
            $logger->error($e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            $returnedData = [
                'success' => false,
                'errorUrl' => $this->context->link->getModuleLink((string) $this->module->name, 'redirect', ['action' => 'paymentError']),
                'message' => $this->module->l('An error occurred while processing your payment. Please try again or contact our customer support', 'payment'),
            ];
        }

        die(json_encode($returnedData));
    }

    /**
     * @return void
     */
    private function checkBancomatPayStatus(): void
    {
        $idCart = (int) Tools::getValue('idCart');
        $token = Tools::getValue('token');
        $cartSecureKey = Tools::getValue('cartSecureKey');

        if (!Validate::isUnsignedId($idCart) || $token !== Tools::getToken()) {
            die(json_encode(['success' => false]));
        }

        $cart = new Cart($idCart);
        if (!Validate::isLoadedObject($cart) || !hash_equals($cart->secure_key, $cartSecureKey)) {
            die(json_encode(['success' => false]));
        }

        $order = \HiPay\PrestaShop\Utils\Tools::getOrderByCartId($idCart);
        if (!\Validate::isLoadedObject($order)) {
            die(json_encode(['success' => true, 'status' => 'pending']));
        }

        $currentState = (int) $order->current_state;

        $failedStates = [
            (int) \Configuration::getGlobalValue('PS_OS_CANCELED'),
            (int) \Configuration::getGlobalValue('PS_OS_ERROR'),
        ];
        $successStates = [
            (int) \Configuration::getGlobalValue('PS_OS_PAYMENT'),
            (int) \Configuration::getGlobalValue('HIPAYPAYMENTS_OS_PENDING_CAPTURE'),
        ];

        if (in_array($currentState, $failedStates)) {
            die(json_encode(['success' => true, 'status' => 'failed']));
        }

        if (!in_array($currentState, $successStates)) {
            die(json_encode(['success' => true, 'status' => 'pending']));
        }

        $customer = new Customer($order->id_customer);
        $redirectUrl = $this->context->link->getPageLink(
            'order-confirmation',
            null,
            null,
            [
                'id_cart' => $idCart,
                'id_module' => $this->module->id,
                'key' => $customer->secure_key,
            ]
        );

        die(json_encode(['success' => true, 'status' => 'success', 'redirectUrl' => $redirectUrl]));
    }
}
