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

namespace HiPay\PrestaShop\Builder;

use AG\PSModuleUtils\Utils\AmountOfMoney;
use HiPay\Fullservice\Gateway\Request\Info\CustomerBillingInfoRequest;
use HiPay\Fullservice\Gateway\Request\Order\HostedPaymentPageRequest;
use HiPay\Fullservice\Gateway\Request\Order\OrderRequest;
use HiPay\Fullservice\Gateway\Request\PaymentMethod\CardTokenPaymentMethod;
use HiPay\PrestaShop\Settings\Entity\AbstractAdvancedPaymentMethod;
use HiPay\PrestaShop\Settings\Entity\CardPaymentSettings;
use HiPay\PrestaShop\Settings\Settings;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class AbstractPaymentRequestBuilder
 */
abstract class AbstractPaymentRequestBuilder implements PaymentRequestBuilderInterface
{
    /** @var AbstractAdvancedPaymentMethod */
    protected $paymentMethod;

    /** @var string */
    protected $paymentMethodIdentifier;

    /** @var \Cart */
    protected $cart;

    /** @var \HiPayPayments */
    protected $module;

    /** @var Settings */
    protected $settings;

    /** @var mixed[] */
    protected $data;

    /**
     * @param string $paymentMethodIdentifier
     * @param \Cart $cart
     * @param \HiPayPayments $module
     * @param Settings $settings
     * @param mixed[] $data
     * @throws \Exception
     */
    public function __construct(string $paymentMethodIdentifier, \Cart $cart, \HiPayPayments $module, Settings $settings, array $data)
    {
        $this->paymentMethodIdentifier = $paymentMethodIdentifier;
        $this->cart = $cart;
        $this->module = $module;
        $this->settings = $settings;
        $paymentMethod = $this->settings->otherPMSettings->findByCode($paymentMethodIdentifier);
        if (false === $paymentMethod || false === $paymentMethod->isEligibleWithCart($cart)) {
            throw new \Exception('Payment method not found or not eligible with cart');
        }
        $this->paymentMethod = $paymentMethod;
        $this->data = $data;
    }

    /**
     * @param OrderRequest|HostedPaymentPageRequest $request
     * @return void
     * @throws \Exception
     */
    protected function configureBaseFields(&$request)
    {
        $billingAddress = new \Address($this->cart->id_address_invoice);
        $shippingAddress = new \Address($this->cart->id_address_delivery);
        $context = \Context::getContext();
        $customer = new \Customer((int) $this->cart->id_customer);

        $request->orderid = sprintf('%d-%s', $this->cart->id, \AG\PSModuleUtils\Tools::generateRandomString(8));
        $request->operation = \HiPay\PrestaShop\Settings\Entity\MainSettings::OPERATION_VALUE[$this->settings->mainSettings->captureMode];
        $request->description = sprintf('ref_%d', $this->cart->id);
        $request->customerBillingInfo = new CustomerBillingInfoRequest();
        $request->customerBillingInfo->firstname = $customer->firstname;
        $request->customerBillingInfo->lastname = $customer->lastname;
        $request->customerBillingInfo->country = (string)\Country::getIsoById($billingAddress->id_country);
        $request->customerBillingInfo->city = $billingAddress->city;
        $request->customerBillingInfo->email = $customer->email;
        $request->customerBillingInfo->phone = $billingAddress->phone ?: $billingAddress->phone_mobile;
        $request->customerBillingInfo->recipientinfo = $billingAddress->company;
        $request->customerBillingInfo->streetaddress = $billingAddress->address1;
        $request->customerBillingInfo->streetaddress2 = $billingAddress->address2;
        $request->customerBillingInfo->zipcode = $billingAddress->postcode;
        $request->customerShippingInfo = new \HiPay\Fullservice\Gateway\Request\Info\CustomerShippingInfoRequest();
        $request->customerShippingInfo->shipto_firstname = $customer->firstname;
        $request->customerShippingInfo->shipto_lastname = $customer->lastname;
        $request->customerShippingInfo->shipto_country = (string)\Country::getIsoById($shippingAddress->id_country);
        $request->customerShippingInfo->shipto_city = $shippingAddress->city;
        $request->customerShippingInfo->shipto_phone = $shippingAddress->phone ?: $shippingAddress->phone_mobile;
        $request->customerShippingInfo->shipto_recipientinfo = $shippingAddress->company;
        $request->customerShippingInfo->shipto_streetaddress = $shippingAddress->address1;
        $request->customerShippingInfo->shipto_streetaddress2 = $shippingAddress->address2;
        $request->customerShippingInfo->shipto_zipcode = $shippingAddress->postcode;
        $request->currency = \Currency::getIsoCodeById($this->cart->id_currency);
        $request->amount = AmountOfMoney::fromStandardUnit($this->cart->getOrderTotal(), \Currency::getIsoCodeById($this->cart->id_currency))->getAmount();
        $request->shipping = AmountOfMoney::fromStandardUnit($this->cart->getTotalShippingCost(), \Currency::getIsoCodeById($this->cart->id_currency))->getAmount();
        if (isset($this->data['moto'])) {
            $request->accept_url = \Tools::getHttpHost(true).$context->link->getAdminLink('AdminOrders', true, [], ['orderId' => $this->data['orderId'], 'vieworder' => 1, 'id_order' => $this->data['orderId'], 'hiPayReturnType' => 'accept']);
            $request->decline_url = \Tools::getHttpHost(true).$context->link->getAdminLink('AdminOrders', true, [], ['orderId' => $this->data['orderId'], 'vieworder' => 1, 'id_order' => $this->data['orderId'], 'hiPayReturnType' => 'decline']);
            $request->pending_url = \Tools::getHttpHost(true).$context->link->getAdminLink('AdminOrders', true, [], ['orderId' => $this->data['orderId'], 'vieworder' => 1, 'id_order' => $this->data['orderId'], 'hiPayReturnType' => 'pending']);
            $request->exception_url = \Tools::getHttpHost(true).$context->link->getAdminLink('AdminOrders', true, [], ['orderId' => $this->data['orderId'], 'vieworder' => 1, 'id_order' => $this->data['orderId'], 'hiPayReturnType' => 'exception']);
            $request->cancel_url = \Tools::getHttpHost(true).$context->link->getAdminLink('AdminOrders', true, [], ['orderId' => $this->data['orderId'], 'vieworder' => 1, 'id_order' => $this->data['orderId'], 'hiPayReturnType' => 'cancel']);
        } else {
            $request->accept_url = $context->link->getModuleLink((string) $this->module->name, 'redirect', ['action' => 'redirectFromPayment', 'returnType' => 'accept']);
            $request->decline_url = $context->link->getModuleLink((string) $this->module->name, 'redirect', ['action' => 'redirectFromPayment', 'returnType' => 'decline']);
            $request->pending_url = $context->link->getModuleLink((string) $this->module->name, 'redirect', ['action' => 'redirectFromPayment', 'returnType' => 'pending']);
            $request->exception_url = $context->link->getModuleLink((string) $this->module->name, 'redirect', ['action' => 'redirectFromPayment', 'returnType' => 'exception']);
            $request->cancel_url = $context->link->getModuleLink((string) $this->module->name, 'redirect', ['action' => 'redirectFromPayment', 'returnType' => 'cancel']);
        }
        $request->notify_url = $context->link->getModuleLink((string) $this->module->name, 'notify');
        $request->http_accept = '*/*';
        $request->language = str_replace('-', '_', $context->language->locale);
        $request->paymentMethod = new CardTokenPaymentMethod();
        $request->paymentMethod->authentication_indicator = CardPaymentSettings::THREE_DS_MODES[$this->settings->cardPaymentSettings->threeDSMode];
        if (isset($this->data['moto'])) {
            $request->paymentMethod->eci = 1;
        } else {
            $request->paymentMethod->eci = 7;
        }
        $request->source = ['source' => 'CMS', 'brand' => 'prestashop', 'brand_version' => _PS_VERSION_, 'integration_version' => $this->module->version];
    }

    /**
     * @param HostedPaymentPageRequest $request
     * @param bool $iframe
     * @return void
     */
    protected function configureHostedPageFields(HostedPaymentPageRequest &$request, bool $iframe = false)
    {
        if (null !== $this->paymentMethod) {
            $request->payment_product_list = $this->paymentMethod->code;
        }
        if (true === $iframe) {
            $request->template = 'iframe-js';
            $request->display_cancel_button = false;
        } else {
            $request->display_cancel_button = isset($this->data['moto']) || $this->settings->cardPaymentSettings->cancelButtonDisplayed;
        }
    }

    /**
     * @return HostedPaymentPageRequest|OrderRequest
     * @throws \Exception
     */
    public function buildRequest()
    {
        if (AbstractAdvancedPaymentMethod::APM_DISPLAY_REDIRECT === $this->paymentMethod->displayMode) {
            $request = new HostedPaymentPageRequest();
            $this->configureBaseFields($request);
            $this->configureHostedPageFields($request);
        } else {
            $request = new OrderRequest();
            $this->configureBaseFields($request);
        }

        return $request;
    }
}
