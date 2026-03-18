<?php
/*
 * MIT License
 *
 * Copyright (c) 2022 Anthony Girard
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 */

namespace AG\PSModuleUtils\Utils;

use Alcohol\ISO4217;
use Money\Currency;
use Money\Money;
use PrestaShop\Decimal\DecimalNumber;
use PrestaShop\Decimal\Operation\Division;
use PrestaShop\Decimal\Operation\Multiplication;
use PrestaShop\Decimal\Operation\Rounding;

/**
 * Class AmountOfMoney
 * @package AG\PSModuleUtils\Utils
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
        $amountComputed = $division->compute(new DecimalNumber((string) $amountInSmallestUnit), new DecimalNumber((string) $exp));
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
        $amountComputed = $multiplication->compute(new DecimalNumber((string) $amountInStandardUnit), new DecimalNumber((string) $exp));
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
}
