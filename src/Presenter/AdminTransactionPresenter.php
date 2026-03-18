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
use HiPay\Fullservice\Gateway\Model\Transaction;
use HiPay\PrestaShop\Utils\AmountOfMoney;
use HiPay\PrestaShop\Settings\Settings;
use HiPay\PrestaShop\Settings\SettingsLoader;
use PrestaShop\Decimal\Exception\DivisionByZeroException;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class AdminTransactionPresenter
 */
class AdminTransactionPresenter implements PresenterInterface
{
    /** @var SettingsLoader */
    private $settingsLoader;

    /**
     * AdminTransactionPresenter Constructor.
     *
     * @param SettingsLoader $settingsLoader
     */
    public function __construct(SettingsLoader $settingsLoader)
    {
        $this->settingsLoader = $settingsLoader;
    }

    /**
     * @param Transaction $object
     * @param int|null $idShop
     * @return mixed[]
     * @throws DivisionByZeroException
     */
    public function present($object, int $idShop = null): array
    {
        $settings = $this->settingsLoader->withContext($idShop, null, true);
        $paymentMethod = $settings->cardPaymentSettings->findByCode($object->getPaymentProduct());
        if (false === $paymentMethod) {
            $paymentMethod = $settings->otherPMSettings->findByCode($object->getPaymentProduct());
        }
        if (null !== $object->getThreeDSecure() && null !== $object->getThreeDSecure()->getAuthenticationStatus()) {
            $threeDSecure = $object->getThreeDSecure()->getAuthenticationStatus() === 'Y' ? 1 : 0;
        } else {
            $threeDSecure = -1;
        }
        $authorizedAmount = AmountOfMoney::fromStandardUnit($object->getAuthorizedAmount(), $object->getCurrency());
        $capturedAmount = AmountOfMoney::fromStandardUnit($object->getCapturedAmount(), $object->getCurrency());
        $capturableAmount = \HiPay\PrestaShop\Utils\AmountOfMoney::subtract($authorizedAmount, $capturedAmount, $object->getCurrency());
        $refundedAmount = AmountOfMoney::fromStandardUnit($object->getRefundedAmount(), $object->getCurrency());
        if ($paymentMethod && true === $paymentMethod->canRefund) {
            $refundableAmount = \HiPay\PrestaShop\Utils\AmountOfMoney::subtract($capturedAmount, $refundedAmount, $object->getCurrency());
            $isRefundable = $refundableAmount->compare(AmountOfMoney::fromStandardUnit(0, $object->getCurrency())) !== 0;
        } else {
            $refundableAmount = AmountOfMoney::fromStandardUnit(0, $object->getCurrency());
            $isRefundable = false;
        }

        return [
            'hipayOrderId' => $object->getOrder()->getId(),
            'reference' => $object->getTransactionReference(),
            'status' => strtoupper($object->getState()),
            'total' => $object->getAuthorizedAmount().' '.$object->getCurrency(),
            'paymentProduct' => $paymentMethod ? $paymentMethod->name : '-',
            '3DSGuarantee' => $threeDSecure,
            'capturedAmountDisplay' => $capturedAmount->formatPrice(),
            'capturableAmountDisplay' => $capturableAmount->formatPrice(),
            'capturedAmount' => $capturedAmount->formatPrice(),
            'capturableAmount' => $capturableAmount->formatPrice(),
            'isCapturable' => $capturableAmount->compare(AmountOfMoney::fromStandardUnit(0, $object->getCurrency())),
            'refundedAmount' => $refundedAmount->formatPrice(),
            'refundableAmount' => $refundableAmount->formatPrice(),
            'isRefundable' => $isRefundable,
        ];
    }
}
