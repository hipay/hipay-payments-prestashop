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
use HiPay\PrestaShop\Settings\Entity\APM\Multibanco;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class MultibancoRequestBuilder
 */
class MultibancoRequestBuilder extends AbstractPaymentRequestBuilder
{
    /**
     * @return HostedPaymentPageRequest|OrderRequest
     * @throws \Exception
     */
    public function buildRequest()
    {
        $request = parent::buildRequest();

        /** @var Multibanco $multibanco */
        $multibanco = $this->settings->otherPMSettings->findByCode('multibanco');
        $request->expiration_limit = $multibanco->expirationLimit;

        return $request;
    }
}
