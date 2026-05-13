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

use HiPay\PrestaShop\Logger\LoggerFactory;
use HiPay\PrestaShop\Settings\Entity\AdvancedPaymentSettings;
use HiPay\PrestaShop\Settings\Entity\CardPaymentSettings;
use HiPay\PrestaShop\Settings\Entity\PaymentMethod;
use HiPay\PrestaShop\Settings\Settings;
use HiPay\PrestaShop\Settings\SettingsLoader;
use Monolog\Logger;

/**
 * Class PaymentRequestBuilderFactory
 */
class PaymentRequestBuilderFactory
{
    /** @var \HiPayPayments */
    private $module;

    /** @var Settings */
    private $settings;

    /**
     * @param \HiPayPayments $module
     * @param SettingsLoader $settingsLoader
     */
    public function __construct(\HiPayPayments $module, SettingsLoader $settingsLoader)
    {
        $this->module = $module;
        $this->settings = $settingsLoader->withContext(\Context::getContext()->shop->id, \Context::getContext()->shop->id_shop_group, true);
    }

    /**
     * @param mixed[] $data
     * @param \Cart $cart
     * @param bool $isCardPayment
     * @return AbstractPaymentRequestBuilder
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function create(array $data, \Cart $cart, bool $isCardPayment = false)
    {
        $paymentMethodCode = $data['payment_product'] ?? '';
        if (true === $isCardPayment) {
            return new CardRequestBuilder(
                $paymentMethodCode,
                $cart,
                $this->module,
                $this->settings,
                $data
            );
        }

        $builder = AdvancedPaymentSettings::APM_CODES[$paymentMethodCode]['builder'];

        return new $builder(
            $paymentMethodCode,
            $cart,
            $this->module,
            $this->settings,
            $data
        );
    }
}
