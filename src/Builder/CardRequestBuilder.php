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

use HiPay\Fullservice\Enum\ThreeDSTwo\DeviceChannel;
use HiPay\Fullservice\Enum\ThreeDSTwo\NameIndicator;
use HiPay\Fullservice\Enum\ThreeDSTwo\ReorderIndicator;
use HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\AccountInfo;
use HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\BrowserInfo;
use HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\MerchantRiskStatement;
use HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\PreviousAuthInfo;
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
            if (false === $paymentMethod) {
                $paymentMethod = $this->settings->otherPMSettings->findByCode($code);
            }
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
        $source = $this->data['source'] ?? '';
        if (
            !isset($this->data['forceHostedPage']) &&
            (
                (CardPaymentSettings::DISPLAY_MODE_HOSTED_FIELDS === $this->settings->cardPaymentSettings->displayMode && !isset($this->data['moto'])) ||
                (CardPaymentSettings::DISPLAY_MODE_HOSTED_PAGE === $this->settings->cardPaymentSettings->displayMode && 'APPLE-PAY' === $source)
            )
        ) {
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
        $request->custom_data = [
            'multi_use' => $this->data['multi_use'] ?? null,
            'one_click' => $this->data['one_click'] ?? null,
        ];
        if (isset($this->data['device_fingerprint'])) {
            $request->device_fingerprint = $this->data['device_fingerprint'];
        }
        $browserInfo = new BrowserInfo();
        $browserInfo->java_enabled = $this->data['browser_info']['java_enabled'] ?? null;
        $browserInfo->javascript_enabled = $this->data['browser_info']['javascript_enabled'] ?? false;
        $browserInfo->language = $this->data['browser_info']['language'] ?? null;
        $browserInfo->color_depth = $this->data['browser_info']['color_depth'] ?? null;
        $browserInfo->screen_height = $this->data['browser_info']['screen_height'] ?? null;
        $browserInfo->screen_width = $this->data['browser_info']['screen_width'] ?? null;
        $browserInfo->timezone = $this->data['browser_info']['timezone'] ?? null;
        $browserInfo->http_user_agent = $this->data['browser_info']['http_user_agent'] ?? null;
        $browserInfo->ipaddr = \Tools::getRemoteAddr();
        $browserInfo->http_accept = $this->data['browser_info']['http_accept'] ?? null;
        $request->browser_info = $browserInfo;
        $request->device_channel = DeviceChannel::BROWSER;

        $customer = new \Customer((int) $this->cart->id_customer);
        if (!$customer->isGuest()) {
            $merchantRiskStatement = new MerchantRiskStatement();
            $merchantRiskStatement->reorder_indicator = $this->getOrderUniquenessInfo($customer->id, \Context::getContext()->cart->id) > 0 ? ReorderIndicator::REORDERED : ReorderIndicator::FIRST_TIME_ORDERED;
            $request->merchant_risk_statement = $merchantRiskStatement;

            $accountInfo = new AccountInfo();

            $customerAccountInfo = new AccountInfo\Customer();
            $customerAccountInfo->account_change = (int) date('Ymd', (int) strtotime($customer->date_upd));
            $customerAccountInfo->opening_account_date = (int) date('Ymd', (int) strtotime($customer->date_add));
            $customerAccountInfo->password_change = (int) date('Ymd', (int) strtotime($customer->last_passwd_gen));

            $paymentAccountInfo = new AccountInfo\Payment();
            if (isset($this->data['one_click']) && $this->data['one_click']) {
                $customerCard = \HiPayPaymentsCustomerCard::getCustomerCardByCustomerIdToken($customer->id, $this->data['token']);
                if ($customerCard->id) {
                    $paymentAccountInfo->enrollment_date = (int) date('Ymd', (int) strtotime($customerCard->date_add));
                }
            }

            $purchaseAccountInfo = new AccountInfo\Purchase();
            $customerOrders = \Order::getCustomerOrders($customer->id);
            if ($customerOrders) {
                $sixMonthAgo = new \DateTime('6 months ago');
                $sixMonthAgo = $sixMonthAgo->format('Y-m-d H:i:s');
                $customerOrders = array_filter($customerOrders, function($order) use ($sixMonthAgo) {
                    return $order['date_add'] >= $sixMonthAgo;
                });
            }
            $purchaseAccountInfo->count = count($customerOrders);

            $shippingAccountInfo = new AccountInfo\Shipping();
            $deliveryAddressId = \Context::getContext()->cart->id_address_delivery;
            if ($customerOrders) {
                $customerOrders = array_filter($customerOrders, function($order) use($deliveryAddressId) {
                    return (int) $order['id_address_delivery'] === (int) $deliveryAddressId;
                });
                if (!empty($customerOrders)) {
                    end($customerOrders);
                    $lastOrder = current($customerOrders);
                    $shippingAccountInfo->shipping_used_date = (int) date('Ymd', (int) strtotime($lastOrder['date_add']));
                }
            }
            $deliveryAddress = new \Address((int) $deliveryAddressId);
            $customerFullName = strtoupper($customer->firstname.$customer->lastname);
            $shippingFullName = strtoupper($deliveryAddress->firstname.$deliveryAddress->lastname);
            $shippingAccountInfo->name_indicator = NameIndicator::DIFFERENT;
            if ($customerFullName === '' || $customerFullName === $shippingFullName) {
                $shippingAccountInfo->name_indicator = NameIndicator::IDENTICAL;
            }

            $accountInfo->customer = $customerAccountInfo;
            $accountInfo->payment = $paymentAccountInfo;
            $accountInfo->purchase = $purchaseAccountInfo;
            $accountInfo->shipping = $shippingAccountInfo;
            $request->account_info = $accountInfo;
        }

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

    /**
     * @param int $idCustomer
     * @param int $idCart
     * @return int
     */
    private function getOrderUniquenessInfo(int $idCustomer, int $idCart): int
    {
        $sql = '
            SELECT COUNT(*)
            FROM (
                SELECT o_hist.id_order
                FROM '._DB_PREFIX_.'cart_product cp_ref
                JOIN '._DB_PREFIX_.'cart c
                    ON  c.id_cart     = cp_ref.id_cart
                    AND c.id_customer = '.(int) $idCustomer.'
                JOIN ' . _DB_PREFIX_.'orders o_hist
                    ON o_hist.id_customer = '.(int) $idCustomer . '
                JOIN '._DB_PREFIX_.'order_detail od_hist
                    ON  od_hist.id_order             = o_hist.id_order
                    AND od_hist.product_id           = cp_ref.id_product
                    AND od_hist.product_attribute_id = cp_ref.id_product_attribute
                    AND od_hist.product_quantity     = cp_ref.quantity
                WHERE cp_ref.id_cart = '.(int) $idCart.'
                GROUP BY o_hist.id_order
                HAVING
                    COUNT(*) = (SELECT COUNT(*) FROM '._DB_PREFIX_.'cart_product WHERE id_cart = '.(int) $idCart.')
                    AND COUNT(*) = (SELECT COUNT(*) FROM '._DB_PREFIX_.'order_detail WHERE id_order = o_hist.id_order)
            ) AS duplicates';

        return (int) \Db::getInstance()->getValue($sql);
    }
}
