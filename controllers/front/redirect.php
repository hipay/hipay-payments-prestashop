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
 * Class HiPayPaymentsRedirectModuleFrontController
 */
class HiPayPaymentsRedirectModuleFrontController extends ModuleFrontController
{
    /** @var HiPayPayments */
    public $module;

    /** @var \HiPay\PrestaShop\Settings\Settings */
    private $settings;

    /** @var \Monolog\Logger */
    private $logger;

    /**
     * HiPayPaymentsRedirectModuleFrontController Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        /** @var \HiPay\PrestaShop\Settings\Settings $settings */
        $settings = $this->module->getService('hp.settings');
        $this->settings = $settings;
        /** @var \HiPay\PrestaShop\Logger\LoggerFactory $loggerFactory */
        $loggerFactory = $this->module->getService('hp.logger.factory');
        $this->logger = $loggerFactory->withChannel('Redirect');
    }

    /**
     * @return mixed[]
     */
    public function getTemplateVarPage(): array
    {
        $page = parent::getTemplateVarPage();
        $page['meta']['robots'] = 'noindex';

        return $page;
    }

    /**
     * @return true
     */
    public function setMedia(): bool
    {
        parent::setMedia();
        $this->registerJavascript(
            'hipay-payments-js-redirect',
            $this->module->getPathUri().'views/js/redirect.js?version='.$this->module->version,
            ['position' => 'bottom', 'priority' => 201, 'server' => 'remote']
        );

        return true;
    }

    /**
     * @return bool
     * @throws SmartyException
     * @throws Exception
     */
    public function display(): bool
    {
        switch (Tools::getValue('action')) {
            case 'redirectToCardPayment':
                $this->redirectToCardPayment();
                break;
            case 'redirectToAPM':
                $this->redirectToAPM();
                break;
            case 'redirectToCardPaymentIframe':
                $this->redirectToCardPayment(true);
                break;
            case 'redirectFromPaymentIframe':
            case 'redirectFromPayment':
                $this->redirectFromPayment();
                break;
            default:
                break;
        }

        return parent::display();
    }

    /**
     * @param bool $iframe
     * @return void
     * @throws Exception
     */
    public function redirectToCardPayment(bool $iframe = false): void
    {
        /** @var \HiPay\PrestaShop\Builder\PaymentRequestBuilderFactory $requestBuilderFactory */
        $requestBuilderFactory = $this->module->getService('hp.payment_request.builder');
        try {
            /** @var \HiPay\PrestaShop\Builder\CardRequestBuilder $builder */
            $builder = $requestBuilderFactory->create(['payment_product' => Tools::getValue('paymentMethodCodes')], $this->context->cart, true);
        } catch (Exception $e) {
            $this->logger->error(
                'Unable to redirect customer to hosted payment page',
                [
                    'message' => $e->getMessage(),
                    'cartId' => $this->context->cart->id,
                    'paymentMethod' => Tools::getValue('paymentMethodCodes'),
                ]
            );
            \Tools::redirect(\Context::getContext()->link->getPageLink('order', null, null, ['step' => 3, 'hipayError' => 1]));
            exit;
        }
        /** @var \HiPay\Fullservice\Gateway\Request\Order\HostedPaymentPageRequest $hostedPaymentRequest */
        $hostedPaymentRequest = $builder->buildRequest($iframe);

        /** @var \HiPay\PrestaShop\Api\PrestaShopSDK $sdk */
        $sdk = $this->module->getService('hp.sdk.gateway');

        $transaction = $sdk
            ->init()
            ->server()
            ->requestHostedPaymentPage($hostedPaymentRequest);
        $forwardUrl = $transaction->getForwardUrl();
        Tools::redirect($forwardUrl);
        exit;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function redirectToAPM(): void
    {
        $paymentMethodCode = Tools::getValue('paymentMethodCode');
        /** @var \HiPay\PrestaShop\Builder\PaymentRequestBuilderFactory $requestBuilderFactory */
        $requestBuilderFactory = $this->module->getService('hp.payment_request.builder');
        try {
            /** @var \HiPay\PrestaShop\Builder\AbstractPaymentRequestBuilder $builder */
            $builder = $requestBuilderFactory->create(['payment_product' => $paymentMethodCode], $this->context->cart);
        } catch (Exception $e) {
            $this->logger->error(
                'Unable to redirect customer to hosted payment page',
                [
                    'message' => $e->getMessage(),
                    'cartId' => $this->context->cart->id,
                    'paymentMethod' => $paymentMethodCode,
                ]
            );
            \Tools::redirect(\Context::getContext()->link->getPageLink('order', null, null, ['step' => 3, 'hipayError' => 1]));
            exit;
        }
        /** @var \HiPay\Fullservice\Gateway\Request\Order\HostedPaymentPageRequest $hostedPaymentRequest */
        $hostedPaymentRequest = $builder->buildRequest();
        $hostedPaymentRequest->payment_product = Tools::getValue('paymentMethodCode');

        /** @var \HiPay\PrestaShop\Api\PrestaShopSDK $sdk */
        $sdk = $this->module->getService('hp.sdk.gateway');

        $transaction = $sdk
            ->init()
            ->server()
            ->requestHostedPaymentPage($hostedPaymentRequest);
        $forwardUrl = $transaction->getForwardUrl();
        Tools::redirect($forwardUrl);
        exit;
    }

    /**
     * @return void
     * @throws PrestaShopException
     */
    public function redirectFromPayment()
    {
        switch (Tools::getValue('returnType')) {
            case 'pending':
            case 'accept':
            case 'completed':
                $this->setTemplate('module:hipaypayments/views/templates/front/redirectFromPayment.tpl');
                break;
            case 'cancel':
                \Tools::redirect($this->context->link->getPageLink('order', null, null, ['step' => 3]));
                exit;
            case 'decline':
            case 'error':
            case 'exception':
            default:
                \Tools::redirect($this->context->link->getPageLink('order', null, null, ['step' => 3, 'hipayError' => 1]));
                exit;
        }

        $hipayOrderId = \Tools::getValue('orderid');
        $pos = strpos($hipayOrderId, '-');
        $idCart = ($pos !== false) ? substr($hipayOrderId, 0, $pos) : false;

        $this->context->smarty->assign([
            'hipayRedirectController' => $this->context->link->getModuleLink((string) $this->module->name, 'redirect', ['action' => 'redirectConfirmation']),
            'hipayCustomerToken' => Tools::getToken(),
            'hipayTransactionReference' => Tools::getValue('reference'),
            'hipayOrderId' => Tools::getValue('orderid'),
            'idCart' => $idCart,
        ]);
    }

    /**
     * @return void
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function displayAjaxRedirectConfirmation()
    {
        $cart = new Cart((int) Tools::getValue('idCart'));
        if (Validate::isLoadedObject($cart) && Validate::isLoadedObject(\HiPay\PrestaShop\Utils\Tools::getOrderByCartId($cart->id))) {
            $customer = new Customer($cart->id_customer);
            die(json_encode([
                'redirectUrl' => $this->context->link->getPageLink(
                    'order-confirmation',
                    null,
                    null,
                    [
                        'id_cart' => $cart->id,
                        'id_module' => $this->module->id,
                        'key' => $customer->secure_key,
                    ]
                ),
            ]));
        }
    }
}
