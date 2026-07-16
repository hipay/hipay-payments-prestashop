<?php
/**
 * 2026 HiPay
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0).
 * It is also available through the world-wide-web at this URL: https://opensource.org/licenses/AFL-3.0
 *
 * @author    HiPay partner
 * @copyright 2026
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

namespace HiPay\PrestaShop\Builder;

use HiPay\Fullservice\Gateway\Request\Order\HostedPaymentPageRequest;
use HiPay\Fullservice\Gateway\Request\Order\OrderRequest;
use HiPay\PrestaShop\Settings\Entity\MainSettings;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class BizumRequestBuilder
 */
class BizumRequestBuilder extends AbstractPaymentRequestBuilder
{
    /**
     * @param OrderRequest $request
     * @return void
     * @throws \Exception
     */
    protected function configureBaseFields(&$request)
    {
        parent::configureBaseFields($request);
        $request->operation = MainSettings::OPERATION_VALUE[MainSettings::CAPTURE_MODE_AUTO];
        $request->customerBillingInfo->phone = $this->data['phone'];
        $context = \Context::getContext();
        $request->accept_url = $context->link->getModuleLink(
            (string) $this->module->name,
            'redirect',
            ['action' => 'redirectFromPayment', 'returnType' => 'pending']
        );
    }
}
