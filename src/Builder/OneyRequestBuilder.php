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

use AG\PSModuleUtils\Tools;
use AG\PSModuleUtils\Utils\AmountOfMoney;
use HiPay\Fullservice\Enum\Customer\Gender;
use HiPay\Fullservice\Gateway\Model\Cart\Cart;
use HiPay\Fullservice\Gateway\Model\Cart\Item;
use HiPay\Fullservice\Gateway\Request\Order\HostedPaymentPageRequest;
use HiPay\Fullservice\Gateway\Request\Order\OrderRequest;
use HiPay\Fullservice\Gateway\Request\PaymentMethod\XTimesCreditCardPaymentMethod;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class OneyRequestBuilder
 */
class OneyRequestBuilder extends AbstractPaymentRequestBuilder
{
    /**
     * @return HostedPaymentPageRequest|OrderRequest
     * @throws \Exception
     */
    public function buildRequest()
    {
        $request = parent::buildRequest();
        $context = \Context::getContext();

        switch ($context->customer->id_gender) {
            case 0:
            default:
                $gender = Gender::UNKNOWN;
                break;
            case 1:
                $gender = Gender::MALE;
                break;
            case 2:
                $gender = Gender::FEMALE;
                break;
        }
        $shippingAddress = new \Address($this->cart->id_address_delivery);

        $date = new \DateTime();
        $date->modify('+2 days');
        $request->cid = sprintf('CUST-%d', $context->customer->id);
        $request->customerBillingInfo->gender = $gender;
        $request->customerShippingInfo->shipto_gender = $gender;
        $request->paymentMethod = new XTimesCreditCardPaymentMethod();
        $request->paymentMethod->shipto_gender = $gender;
        $request->paymentMethod->shipto_phone = $shippingAddress->phone ?: $shippingAddress->phone_mobile;
        $request->paymentMethod->delivery_method = '{"mode":"CARRIER","shipping":"STANDARD"}';
        $request->paymentMethod->delivery_date = $date->format('Y-m-d');
        $request->basket = new Cart();
        $cartCurrencyCode = Tools::getIsoCurrencyCodeById($context->cart->id_currency);
        $total = AmountOfMoney::fromStandardUnit(0, $cartCurrencyCode);
        foreach ($context->cart->getProducts() as $product) {
            $unitPrice = number_format(\Tools::ps_round($product['price_wt'], 3), 3, '.', '');
            $totalPrice = number_format(\Tools::ps_round($product['total_wt'], 3), 3, '.', '');
            $item = new Item();
            $item->setProductReference($product['reference']);
            $item->setName($product['name']);
            $item->setType('good');
            $item->setQuantity($product['cart_quantity']);
            $item->setUnitPrice((float) $unitPrice);
            $item->setTaxRate($product['rate']);
            $item->setTotalAmount((float) $totalPrice);
            $total = AmountOfMoney::sum([$total->getAmount(), $totalPrice], $cartCurrencyCode);
            $request->basket->addItem($item);
        }
        $shippingCost = $context->cart->getTotalShippingCost();
        if ($shippingCost) {
            $shippingCostAmountOfMoney = AmountOfMoney::fromStandardUnit($shippingCost, $cartCurrencyCode);
            $carrier = new \Carrier((int) $context->cart->id_carrier);
            $item = new Item();
            $item->setProductReference(sprintf('SHIP-%s', $context->cart->id_carrier));
            $item->setName('ShippingFees');
            $item->setType('fee');
            $item->setQuantity(1);
            $item->setUnitPrice($shippingCostAmountOfMoney->getAmount());
            $item->setTaxRate($carrier->getTaxesRate(new \Address((int) $context->cart->id_address_delivery)));
            $item->setTotalAmount($shippingCostAmountOfMoney->getAmount());
            $total = AmountOfMoney::sum([$total->getAmount(), $shippingCostAmountOfMoney->getAmount()], $cartCurrencyCode);
            $request->basket->addItem($item);
        }
        if ($context->cart->gift) {
            $giftWrappingAmountOfMoney = AmountOfMoney::fromStandardUnit($context->cart->getOrderTotal(true, \Cart::ONLY_WRAPPING), $cartCurrencyCode);
            $item = new Item();
            $item->setProductReference(sprintf('GIFT-WRAPPING-%s', Tools::generateRandomString(5)));
            $item->setName('GiftWrapping');
            $item->setType('good');
            $item->setQuantity(1);
            $item->setUnitPrice($giftWrappingAmountOfMoney->getAmount());
            $item->setTaxRate(0);
            $item->setTotalAmount($giftWrappingAmountOfMoney->getAmount());
            $total = AmountOfMoney::sum([$total->getAmount(), $giftWrappingAmountOfMoney->getAmount()], $cartCurrencyCode);
            $request->basket->addItem($item);
        }
        $cartRules = (array) $context->cart->getCartRules();
        if ($cartRules) {
            $cartRulesTotal = 0;
            foreach ($cartRules as $cartRule) {
                $cartRulesTotal += \Tools::ps_round($cartRule['value_real'], 3);
            }
            if ($cartRulesTotal) {
                $cartRulesTotalValue = number_format(\Tools::ps_round($cartRulesTotal, 3), 3, '.', '');
                $item = new Item();
                $item->setProductReference(sprintf('DISCOUNTS-%s', Tools::generateRandomString(5)));
                $item->setName('Discounts');
                $item->setType('discount');
                $item->setQuantity(1);
                $item->setUnitPrice((float) $cartRulesTotalValue);
                $item->setTaxRate(0);
                $item->setTotalAmount((float) $cartRulesTotalValue);
                $request->basket->addItem($item);
            }
        }

        return $request;
    }
}
