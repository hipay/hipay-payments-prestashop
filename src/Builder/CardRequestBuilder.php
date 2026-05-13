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

use HiPay\Fullservice\Gateway\Request\Order\HostedPaymentPageRequest;
use HiPay\Fullservice\Gateway\Request\Order\OrderRequest;
use HiPay\PrestaShop\Settings\Entity\CardPaymentSettings;
use HiPay\PrestaShop\Settings\Settings;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class CardRequestBuilder
 */
class CardRequestBuilder extends AbstractPaymentRequestBuilder
{
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
        $this->data = $data;
        $paymentMethodCodes = explode(',', $paymentMethodIdentifier);
        foreach ($paymentMethodCodes as $code) {
            $paymentMethod = $this->settings->cardPaymentSettings->findByCode($code);
            if (false === $paymentMethod || false === $paymentMethod->isEligibleWithCart($cart)) {
                throw new \Exception('Payment method not found or not eligible with cart');
            }
        }
    }

    /**
     * @param bool $iframe
     * @return HostedPaymentPageRequest|OrderRequest
     * @throws \Exception
     */
    public function buildRequest(bool $iframe = false)
    {
        if (CardPaymentSettings::DISPLAY_MODE_HOSTED_FIELDS === $this->settings->cardPaymentSettings->displayMode && !isset($this->data['moto'])) {
            return $this->buildOrderRequest();
        } else {
            return $this->buildHostedPaymentPageRequest($iframe);
        }
    }

    /**
     * @return OrderRequest
     * @throws \Exception
     */
    private function buildOrderRequest(): OrderRequest
    {
        $request = new OrderRequest();
        $this->configureBaseFields($request);
        $request->paymentMethod->cardtoken = $this->data['token'] ?? '';

        return $request;
    }

    /**
     * @param bool $iframe
     * @return HostedPaymentPageRequest
     * @throws \Exception
     */
    private function buildHostedPaymentPageRequest(bool $iframe): HostedPaymentPageRequest
    {
        $request = new HostedPaymentPageRequest();
        $this->configureBaseFields($request);
        $this->configureHostedPageFields($request, $iframe);
        if (isset($this->data['moto']) && isset($this->data['payment_product'])) {
            $request->payment_product_list = $this->data['payment_product'];
        } else {
            $request->payment_product_list = \Tools::getValue('paymentMethodCodes');
        }

        return $request;
    }
}
