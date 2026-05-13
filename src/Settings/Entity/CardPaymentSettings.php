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

namespace HiPay\PrestaShop\Settings\Entity;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class CardPaymentSettings
 */
class CardPaymentSettings
{
    const DISPLAY_MODE_HOSTED_FIELDS = 'hosted_fields';
    const DISPLAY_MODE_HOSTED_PAGE = 'hosted_page';
    const HOSTED_PAGE_TYPE_REDIRECT = 'redirect';
    const HOSTED_PAGE_TYPE_IFRAME = 'iframe';

    const THREE_DS_MODE_DISABLED = 'disabled';
    const THREE_DS_MODE_IF_AVAILABLE = 'if_available';
    const THREE_DS_MODE_ALWAYS = 'always';
    const THREE_DS_MODES = [
        'disabled' => 0,
        'if_available' => 1,
        'always' => 2,
    ];
    const CARDS_PAYMENT_CODES = [
        'cb' => 'Carte Bancaire',
        'visa' => 'Visa',
        'mastercard' => 'Mastercard',
        'american-express' => 'American Express',
        'bcmc' => 'Bancontact',
        'maestro' => 'Maestro',
    ];
    const UNAUTHORIZED_REFUND = ['bcmc'];

    const BUILDER_PAYMENT_CODE = 'credit_card';

    /** @var string */
    public $displayMode;

    /** @var string */
    public $hostedPageType;

    /** @var bool */
    public $cancelButtonDisplayed;

    /** @var bool */
    public $oneClickEnabled;

    /** @var string */
    public $threeDSMode;

    /** @var UISettings */
    public $UISettings;

    /** @var PaymentMethod[] */
    public $paymentMethods;

    /**
     * @param string $code
     * @return bool
     */
    public function hasCard(string $code): bool
    {
        foreach ($this->paymentMethods as $paymentMethod) {
            if ($paymentMethod->code === $code) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return mixed[]
     */
    public function listUnavailableCards(): array
    {
        $unavailableCards = [];
        foreach (self::CARDS_PAYMENT_CODES as $code => $name) {
            if (!$this->hasCard($code)) {
                $unavailableCards[] = $name;
            }
        }

        return $unavailableCards;
    }

    /**
     * @param string $code
     * @return false|PaymentMethod
     */
    public function findByCode(string $code)
    {
        foreach ($this->paymentMethods as $paymentMethod) {
            if ($paymentMethod->code === $code) {
                return $paymentMethod;
            }
        }

        return false;
    }

    /**
     * @param \Cart $cart
     * @return mixed[]
     */
    public function getCardPaymentsCodes(\Cart $cart): array
    {
        $cardPaymentCodes = [];
        foreach ($this->paymentMethods as $cardPaymentMethod) {
            try {
                if (true === $cardPaymentMethod->isEligibleWithCart($cart)) {
                    $cardPaymentCodes[] = $cardPaymentMethod->code;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return $cardPaymentCodes;
    }
}
