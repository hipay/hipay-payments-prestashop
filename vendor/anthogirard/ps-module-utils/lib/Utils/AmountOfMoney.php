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

class AmountOfMoney
{
    private float $amount;
    private int $amountInCents;
    private string $currencyCode;
    private string $currencyNumeric;
    private int $exp;

    /**
     * @param mixed[] $currencyDetails
     */
    private function __construct(int|float $amount, int $amountInCents, array $currencyDetails)
    {
        $this->amount = (float) $amount;
        $this->amountInCents = (int) $amountInCents;
        $this->currencyCode = (string) $currencyDetails['alpha3'];
        $this->currencyNumeric = $currencyDetails['numeric'];
        $this->exp = $currencyDetails['exp'];
    }

    /**
     * @throws \PrestaShop\Decimal\Exception\DivisionByZeroException
     */
    public static function fromSmallestUnit(int|float $amountInSmallestUnit, string $currencyCode): self
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

    public static function fromStandardUnit(int|float $amountInStandardUnit, string $currencyCode): self
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

    public function getAmount(): float
    {
        return (float) number_format($this->amount, $this->exp, '.', '');
    }

    public function getAmountInCents(): int
    {
        return (int) $this->amountInCents;
    }

    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }

    public function getCurrencyNumeric(): string
    {
        return $this->currencyNumeric;
    }

    public function formatPrice(): string
    {
        return sprintf('%s %s', number_format($this->amount, $this->exp, '.', ''), $this->currencyCode);
    }

    public function compare(AmountOfMoney $otherAmountOfMoney): int
    {
        $moneyA = new Money($this->amountInCents, new Currency($this->currencyCode));
        $moneyB = new Money($otherAmountOfMoney->getAmountInCents(), new Currency($otherAmountOfMoney->getCurrencyCode()));

        return $moneyA->compare($moneyB);
    }

    /**
     * @param mixed[] $amounts
     * @throws \PrestaShop\Decimal\Exception\DivisionByZeroException
     */
    public static function sum(array $amounts, string $currencyCode, bool $inSmallestUnit = false): self
    {
        $amounts = array_map(
            fn($item) => $inSmallestUnit ? self::fromSmallestUnit($item, $currencyCode) : self::fromStandardUnit($item, $currencyCode),
            $amounts
        );

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
     * @throws \PrestaShop\Decimal\Exception\DivisionByZeroException
     */
    public static function subtract(AmountOfMoney $amount1, AmountOfMoney $amount2, string $currencyCode): self
    {
        $currency = new Currency($currencyCode);
        $total = new Money($amount1->getAmountInCents(), $currency);
        $subtract = new Money($amount2->getAmountInCents(), $currency);
        $result = $total->subtract($subtract);

        return self::fromSmallestUnit((float) $result->getAmount(), $currencyCode);
    }

    /**
     * Converts the current amount to a target currency by multiplying with the conversion rate.
     */
    public function convertTo(string $targetCurrencyCode, int|float $conversionRate): self
    {
        $iso4217 = new ISO4217();
        $targetCurrencyDetails = $iso4217->getByCode($targetCurrencyCode);

        $multiplication = new Multiplication();
        $convertedAmount = $multiplication->compute(
            new DecimalNumber((string) $this->amount),
            new DecimalNumber((string) $conversionRate)
        );
        $convertedAmountRounded = $convertedAmount->toPrecision($targetCurrencyDetails['exp'], Rounding::ROUND_HALF_UP);

        return self::fromStandardUnit((float) $convertedAmountRounded, $targetCurrencyCode);
    }

    /**
     * Converts the current amount from a source currency by dividing with the conversion rate.
     *
     * @throws \PrestaShop\Decimal\Exception\DivisionByZeroException
     */
    public function convertFrom(string $targetCurrencyCode, int|float $conversionRate): self
    {
        $iso4217 = new ISO4217();
        $targetCurrencyDetails = $iso4217->getByCode($targetCurrencyCode);

        $division = new Division();
        $convertedAmount = $division->compute(
            new DecimalNumber((string) $this->amount),
            new DecimalNumber((string) $conversionRate)
        );
        $convertedAmountRounded = $convertedAmount->toPrecision($targetCurrencyDetails['exp'], Rounding::ROUND_HALF_UP);

        return self::fromStandardUnit((float) $convertedAmountRounded, $targetCurrencyCode);
    }
}
