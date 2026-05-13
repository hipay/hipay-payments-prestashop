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

use HiPay\PrestaShop\Builder as Builder;
use HiPay\PrestaShop\Settings\Entity\APM as APM;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class AdvancedPaymentSettings
 */
class AdvancedPaymentSettings
{
    const APM_CODES = [
        'paypal' => ['name' => 'PayPal', 'builder' => Builder\PayPalRequestBuilder::class, 'discriminatorMap' => APM\PayPal::class],
        'mbway' => ['name' => 'MB Way', 'builder' => Builder\MBWayRequestBuilder::class, 'discriminatorMap' => APM\MBWay::class],
        'multibanco' => ['name' => 'Multibanco', 'builder' => Builder\MultibancoRequestBuilder::class, 'discriminatorMap' => APM\Multibanco::class],
        'ideal' => ['name' => 'iDEAL', 'builder' => Builder\IDealRequestBuilder::class, 'discriminatorMap' => APM\IDeal::class],
        'bancontact' => ['name' => 'Bancontact', 'builder' => Builder\BancontactRequestBuilder::class, 'discriminatorMap' => APM\Bancontact::class],
        'mybank' => ['name' => 'MyBank', 'builder' => Builder\MyBankRequestBuilder::class, 'discriminatorMap' => APM\MyBank::class],
        'sisal' => ['name' => 'Mooney', 'builder' => Builder\MooneyRequestBuilder::class, 'discriminatorMap' => APM\Mooney::class],
        'alma-3x' => ['name' => 'Alma 3x', 'builder' => Builder\Alma3xRequestBuilder::class, 'discriminatorMap' => APM\Alma3x::class],
        'alma-4x' => ['name' => 'Alma 4x', 'builder' => Builder\Alma4xRequestBuilder::class, 'discriminatorMap' => APM\Alma4x::class],
        'applepay' => ['name' => 'Apple Pay', 'builder' => Builder\ApplePayRequestBuilder::class, 'discriminatorMap' => APM\ApplePay::class],
        'przelewy24' => ['name' => 'Przelewy24', 'builder' => Builder\P24RequestBuilder::class, 'discriminatorMap' => APM\P24::class],
        'illicado' => ['name' => 'Illicado', 'builder' => Builder\IllicadoRequestBuilder::class, 'discriminatorMap' => APM\Illicado::class],
        'klarna' => ['name' => 'Klarna', 'builder' => Builder\KlarnaRequestBuilder::class, 'discriminatorMap' => APM\Klarna::class],
        'postfinance-card' => ['name' => 'Postfinance', 'builder' => Builder\PostfinanceRequestBuilder::class, 'discriminatorMap' => APM\Postfinance::class],
        '3xcb' => ['name' => 'Oney - 3x Carte Bancaire', 'builder' => Builder\OneyRequestBuilder::class, 'discriminatorMap' => APM\Oney::class],
        '4xcb' => ['name' => 'Oney - 4x Carte Bancaire', 'builder' => Builder\OneyRequestBuilder::class, 'discriminatorMap' => APM\Oney::class],
        '3xcb-no-fees' => ['name' => 'Oney - 3x Carte Bancaire sans frais', 'builder' => Builder\OneyRequestBuilder::class, 'discriminatorMap' => APM\Oney::class],
        '4xcb-no-fees' => ['name' => 'Oney - 4x Carte Bancaire sans frais', 'builder' => Builder\OneyRequestBuilder::class, 'discriminatorMap' => APM\Oney::class],
    ];

    /** @var AbstractAdvancedPaymentMethod[] */
    public $paymentMethods;

    /**
     * @param string $code
     * @return bool
     */
    public function isInstalled(string $code): bool
    {
        foreach ($this->paymentMethods as $paymentMethod) {
            if ($paymentMethod->code === $code) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $code
     * @return bool
     */
    public function canBeInstalled(string $code): bool
    {
        return isset(self::APM_CODES[$code]);
    }

    /**
     * @return mixed[]
     */
    public function listUnavailableAPM(): array
    {
        $unavailableAPM = [];
        foreach (self::APM_CODES as $code => $details) {
            if (!$this->isInstalled($code)) {
                $unavailableAPM[] = $details['name'];
            }
        }

        return $unavailableAPM;
    }

    /**
     * @param string $code
     * @return false|AbstractAdvancedPaymentMethod
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
     * @return AbstractAdvancedPaymentMethod[]
     */
    public function getAPMDetails(\Cart $cart): array
    {
        $apm = [];
        foreach ($this->getPaymentMethods() as $cardPaymentMethod) {
            try {
                if (true === $cardPaymentMethod->isEligibleWithCart($cart)) {
                    $apm[] = $cardPaymentMethod;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return $apm;
    }

    /**
     * @param string[] $modes
     * @return AbstractAdvancedPaymentMethod[]
     */
    public function getPaymentMethodsByDisplayMode(array $modes): array
    {
        return array_filter($this->paymentMethods, function ($paymentMethod) use ($modes) {
            return in_array($paymentMethod->displayMode, $modes);
        });
    }

    /**
     * @return AbstractAdvancedPaymentMethod[]
     */
    public function getPaymentMethods(): array
    {
        $paymentMethods = $this->paymentMethods;
        usort($paymentMethods, function($a, $b) {
            return $a->position <=> $b->position;
        });

        return $paymentMethods;
    }
}
