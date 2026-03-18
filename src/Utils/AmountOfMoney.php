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

namespace HiPay\PrestaShop\Utils;

use Alcohol\ISO4217;
use Money\Currency;
use Money\Money;
use PrestaShop\Decimal\Number;
use PrestaShop\Decimal\Operation\Division;
use PrestaShop\Decimal\Operation\Multiplication;
use PrestaShop\Decimal\Operation\Rounding;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class AmountOfMoney
 */
class AmountOfMoney
{
    /** @var float */
    private $amount;

    /** @var int */
    private $amountInCents;

    /** @var string */
    private $currencyCode;

    /** @var string  */
    private $currencyNumeric;

    /** @var int */
    private $exp;

    /**
     * AmountOfMoney constructor.
     * @param float|int $amount
     * @param int       $amountInCents
     * @param mixed[]   $currencyDetails
     */
    private function __construct($amount, $amountInCents, $currencyDetails)
    {
        $this->amount = (float) $amount;
        $this->amountInCents = (int) $amountInCents;
        $this->currencyCode = (string) $currencyDetails['alpha3'];
        $this->currencyNumeric = $currencyDetails['numeric'];
        $this->exp = $currencyDetails['exp'];
    }

    /**
     * @param float|int $amountInSmallestUnit
     * @param string    $currencyCode
     * @return AmountOfMoney
     * @throws \PrestaShop\Decimal\Exception\DivisionByZeroException
     */
    public static function fromSmallestUnit($amountInSmallestUnit, $currencyCode)
    {
        $iso4217 = new ISO4217();
        $currencyDetails = $iso4217->getByCode($currencyCode);
        $exp = pow(10, $currencyDetails['exp']);

        $amountInSmallestUnit = \Tools::ps_round($amountInSmallestUnit);
        $division = new Division();
        $amountComputed = $division->compute(new Number((string) $amountInSmallestUnit), new Number((string) $exp));
        $amount = $amountComputed->toPrecision($currencyDetails['exp'], Rounding::ROUND_HALF_UP);

        return new self((float) $amount, (int) $amountInSmallestUnit, $currencyDetails);
    }

    /**
     * @param float|int $amountInStandardUnit
     * @param string    $currencyCode
     * @return AmountOfMoney
     */
    public static function fromStandardUnit($amountInStandardUnit, $currencyCode)
    {
        $iso4217 = new ISO4217();
        $currencyDetails = $iso4217->getByCode($currencyCode);
        $exp = pow(10, $currencyDetails['exp']);

        $amountInStandardUnit = \Tools::ps_round($amountInStandardUnit, $currencyDetails['exp']);
        $multiplication = new Multiplication();
        $amountComputed = $multiplication->compute(new Number((string) $amountInStandardUnit), new Number((string) $exp));
        $amount = $amountComputed->toPrecision(0);

        return new self($amountInStandardUnit, (int) $amount, $currencyDetails);
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return (float) number_format($this->amount, $this->exp, '.', '');
    }

    /**
     * @return int
     */
    public function getAmountInCents()
    {
        return (int) $this->amountInCents;
    }

    /**
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->currencyCode;
    }

    /**
     * @return mixed|string
     */
    public function getCurrencyNumeric()
    {
        return $this->currencyNumeric;
    }

    /**
     * @return string
     */
    public function formatPrice()
    {
        return sprintf('%s %s', number_format($this->amount, $this->exp, '.', ''), $this->currencyCode);
    }

    /**
     * @param AmountOfMoney $otherAmountOfMoney
     * @return int
     */
    public function compare(AmountOfMoney $otherAmountOfMoney)
    {
        $moneyA = new Money($this->amountInCents, new Currency($this->currencyCode));
        $moneyB = new Money($otherAmountOfMoney->getAmountInCents(), new Currency($otherAmountOfMoney->getCurrencyCode()));

        return $moneyA->compare($moneyB);
    }

    /**
     * @param mixed[] $amounts
     * @param string  $currencyCode
     * @param bool    $inSmallestUnit
     * @return AmountOfMoney
     * @throws \PrestaShop\Decimal\Exception\DivisionByZeroException
     */
    public static function sum(array $amounts, $currencyCode, $inSmallestUnit = false)
    {
        array_walk($amounts, function(&$item, $key, $args) {
            $item = $args['inSmallestUnit'] ? self::fromSmallestUnit($item, $args['currencyCode']) : self::fromStandardUnit($item, $args['currencyCode']);
        }, ['inSmallestUnit' => $inSmallestUnit, 'currencyCode' => $currencyCode]);

        $currency = new Currency($currencyCode);
        $total = new Money(0, $currency);
        /** @var AmountOfMoney $amount */
        foreach ($amounts as $amount) {
            $addend = new Money($amount->getAmountInCents(), $currency);
            $total = $total->add($addend);
        }

        return self::fromSmallestUnit((float) $total->getAmount(), $currencyCode);
    }

    /**
     * @param AmountOfMoney $amount1
     * @param AmountOfMoney $amount2
     * @param string        $currencyCode
     * @return AmountOfMoney
     * @throws \PrestaShop\Decimal\Exception\DivisionByZeroException
     */
    public static function subtract(AmountOfMoney $amount1, AmountOfMoney $amount2, string $currencyCode): AmountOfMoney
    {
        $currency = new Currency($currencyCode);
        $total = new Money($amount1->getAmountInCents(), $currency);
        $subtract = new Money($amount2->getAmountInCents(), $currency);
        $result = $total->subtract($subtract);

        return self::fromSmallestUnit((float) $result->getAmount(), $currencyCode);
    }
}
