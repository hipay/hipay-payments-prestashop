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
        $logger->debug('Received Hosted Fields payment', $data);

        try {
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
                if ($transaction->getState() === 'completed') {
                    if (isset($data['multi_use']) && $data['multi_use']) {
                        $id = HiPayPaymentsCustomerCard::getCustomerCardIdByPan((string) $data['pan'] ?? '', $this->context->customer->id);
                        $storedCard = new HiPayPaymentsCustomerCard((int) $id);
                        $storedCard->id_customer = (int) $this->context->customer->id;
                        $storedCard->card_token = pSQL($data['token'] ?? '');
                        $storedCard->card_brand = pSQL($data['brand'] ?? '');
                        $storedCard->payment_product = pSQL($data['payment_product'] ?? '');
                        $storedCard->card_pan = pSQL($data['pan'] ?? '');
                        $storedCard->card_expiry_month = pSQL($data['card_expiry_month'] ?? '');
                        $storedCard->card_expiry_year = pSQL($data['card_expiry_year'] ?? '');
                        $storedCard->card_holder = pSQL($data['card_holder'] ?? '');
                        try {
                            $storedCard->save();
                        } catch (PrestaShopException $e) {
                            $logger->error($e->getMessage());
                        }
                    }
                }
                $redirectParams = [
                    'orderid' => $transaction->getOrder()->getId(),
                    'reference' => $transaction->getTransactionReference(),
                    'action' => 'redirectFromPayment',
                    'returnType' => $transaction->getState(),
                ];
                $returnedData = [
                    'success' => true,
                    'redirectUrl' => $this->context->link->getModuleLink((string) $this->module->name, 'redirect', $redirectParams)
                ];
            }
        } catch (Exception $e) {
            $logger->error($e->getMessage());
            $returnedData = [
                'success' => false,
                'message' => $this->module->l('An error occurred while processing your payment. Please try again or contact our customer support', 'payment'),
            ];
        }

        die(json_encode($returnedData));
    }
}
