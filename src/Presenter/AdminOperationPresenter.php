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
use HiPay\PrestaShop\Utils\AmountOfMoney;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class AdminOperationPresenter
 */
class AdminOperationPresenter implements PresenterInterface
{
    /** @var \HiPayPayments */
    private $module;

    /**
     * AdminOperationPresenter Constructor.
     *
     * @param \HiPayPayments $module
     */
    public function __construct(\HiPayPayments $module)
    {
        $this->module = $module;
    }

    /**
     * @param \Order       $object
     * @param mixed[]|null $data
     * @return mixed[]
     * @throws \Exception
     */
    public function present($object, array $data = null): array
    {
        if (!\Validate::isLoadedObject($object)) {
            return [
                'error' => true,
                'message' => $this->module->l('Cannot load order details', 'CapturePresenter'),
            ];
        }
        $orderDetails = $this->getOrderDetails($object, $data);

        return [
            'orderDetails' => $orderDetails,
        ];
    }

    /**
     * @param \Order  $order
     * @param mixed[] $amountData
     * @return mixed[]
     * @throws \Exception
     */
    private function getOrderDetails(\Order $order, array $amountData): array
    {
        $orderDetails = $order->getOrderDetailList();
        $orderCurrency = new \Currency((int) $order->id_currency);
        $orderCurrencyCode = $orderCurrency->iso_code;
        $data = [];
        foreach ($orderDetails as $orderDetail) {
            $unitPrice = AmountOfMoney::fromStandardUnit($orderDetail['unit_price_tax_incl'], $orderCurrencyCode);
            $totalPrice = AmountOfMoney::fromStandardUnit($orderDetail['total_price_tax_incl'], $orderCurrencyCode);
            $originalPrice = AmountOfMoney::fromStandardUnit((1 + ($orderDetail['tax_rate'] / 100)) * $orderDetail['original_product_price'], $orderCurrencyCode);
            $reductionAmount = AmountOfMoney::fromStandardUnit($orderDetail['reduction_amount'], $orderCurrencyCode);

            $data['productDetails'][] = [
                'productName' => $orderDetail['product_name'],
                'productQuantity' => $orderDetail['product_quantity'],
                'unitPrice' => $unitPrice->getAmount(),
                'unitPriceDisplay' => $unitPrice->formatPrice(),
                'totalPrice' => $totalPrice->getAmount(),
                'totalPriceDisplay' => $totalPrice->formatPrice(),
                'originalPrice' => $originalPrice->getAmount(),
                'originalPriceDisplay' => $originalPrice->formatPrice(),
                'wasDisplay' => $orderDetail['unit_price_tax_excl'] !== $orderDetail['original_product_price'],
                'reductionDisplay' => $orderDetail['reduction_percent'] ? sprintf('(-%s%%)', $orderDetail['reduction_percent']) : ($orderDetail['reduction_amount'] ? sprintf('(-%s)', $reductionAmount->formatPrice()) : false),
            ];
        }
        $cart = new \Cart((int) $order->id_cart);
        $orderTotal = AmountOfMoney::fromStandardUnit($cart->getOrderTotal(), $orderCurrencyCode);
        $shippingTotal = AmountOfMoney::fromStandardUnit($order->total_shipping, $orderCurrencyCode);
        $data['orderTotal'] = $orderTotal->getAmount();
        $data['orderTotalDisplay'] = $orderTotal->formatPrice();
        $data['shippingTotal'] = $shippingTotal->getAmount();
        $data['shippingTotalDisplay'] = $shippingTotal->formatPrice();
        if (isset($amountData['amountCaptured']) && isset($amountData['amountCapturable'])) {
            $data['amountCaptured'] = AmountOfMoney::fromStandardUnit($amountData['amountCaptured'], $orderCurrencyCode)->formatPrice();
            $data['amountCapturable'] = AmountOfMoney::fromStandardUnit($amountData['amountCapturable'], $orderCurrencyCode)->getAmount();
        }
        if (isset($amountData['amountRefunded']) && isset($amountData['amountRefundable'])) {
            $data['amountRefunded'] = AmountOfMoney::fromStandardUnit($amountData['amountRefunded'], $orderCurrencyCode)->formatPrice();
            $data['amountRefundable'] = AmountOfMoney::fromStandardUnit($amountData['amountRefundable'], $orderCurrencyCode)->getAmount();
        }
        $data['currencyDecimals'] = $orderCurrency->precision;
        $data['currencyCode'] = $orderCurrencyCode;
        $data['cartRules'] = false;
        $cartRulesTotal = AmountOfMoney::fromStandardUnit(0, $orderCurrencyCode);
        $orderCartRules = $order->getCartRules() ?: [];
        foreach ($orderCartRules as $orderCartRule) {
            $cartRuleValue = AmountOfMoney::fromStandardUnit($orderCartRule['value'], $orderCurrencyCode);
            $cartRulesTotal = AmountOfMoney::sum([$cartRulesTotal->getAmount(), $cartRuleValue->getAmount()], $orderCurrencyCode);

            $data['cartRules'][] = [
                'title' => $orderCartRule['name'],
                'value' => $cartRuleValue->formatPrice(),
            ];
        }
        $data['cartRulesTotalDisplay'] = $cartRulesTotal->formatPrice();

        return $data;
    }
}
