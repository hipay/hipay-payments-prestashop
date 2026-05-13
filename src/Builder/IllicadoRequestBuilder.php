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
use HiPay\Fullservice\Gateway\Request\PaymentMethod\PrepaidCardPaymentMethod;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class IllicadoRequestBuilder
 */
class IllicadoRequestBuilder extends AbstractPaymentRequestBuilder
{
    /**
     * @return HostedPaymentPageRequest|OrderRequest
     * @throws \Exception
     */
    public function buildRequest()
    {
        $request = parent::buildRequest();

        $request->paymentMethod = new PrepaidCardPaymentMethod();
        $request->paymentMethod->prepaid_card_number = $this->data['prepaid_card_number'] ?? '';
        $request->paymentMethod->prepaid_card_security_code = $this->data['prepaid_card_security_code'] ?? '';

        return $request;
    }
}
