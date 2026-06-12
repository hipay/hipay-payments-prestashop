<?php

namespace Tests\AG\PSModuleUtils\Utils;

use AG\PSModuleUtils\Utils\AmountOfMoney;
use PHPUnit\Framework\TestCase;

/**
 * Class AmountOfMoneyTest
 * @package Tests\AG\PSModuleUtils\Utils
 */
class AmountOfMoneyTest extends TestCase
{
    // ========== fromStandardUnit() tests ==========

    /**
     * Tests that fromStandardUnit creates an AmountOfMoney with correct amount.
     *
     * @return void
     */
    public function testFromStandardUnitReturnsCorrectAmount(): void
    {
        $amount = AmountOfMoney::fromStandardUnit(10.50, 'EUR');

        $this->assertEquals(10.50, $amount->getAmount());
    }

    /**
     * Tests that fromStandardUnit converts to cents correctly.
     *
     * @return void
     */
    public function testFromStandardUnitReturnsCorrectAmountInCents(): void
    {
        $amount = AmountOfMoney::fromStandardUnit(10.50, 'EUR');

        $this->assertEquals(1050, $amount->getAmountInCents());
    }

    /**
     * Tests that fromStandardUnit stores the currency code.
     *
     * @return void
     */
    public function testFromStandardUnitStoresCurrencyCode(): void
    {
        $amount = AmountOfMoney::fromStandardUnit(10.50, 'EUR');

        $this->assertEquals('EUR', $amount->getCurrencyCode());
    }

    /**
     * Tests that fromStandardUnit handles zero amount.
     *
     * @return void
     */
    public function testFromStandardUnitHandlesZeroAmount(): void
    {
        $amount = AmountOfMoney::fromStandardUnit(0, 'EUR');

        $this->assertEquals(0.00, $amount->getAmount());
        $this->assertEquals(0, $amount->getAmountInCents());
    }

    /**
     * Tests that fromStandardUnit handles integer amounts.
     *
     * @return void
     */
    public function testFromStandardUnitHandlesIntegerAmount(): void
    {
        $amount = AmountOfMoney::fromStandardUnit(100, 'EUR');

        $this->assertEquals(100.00, $amount->getAmount());
        $this->assertEquals(10000, $amount->getAmountInCents());
    }

    // ========== fromSmallestUnit() tests ==========

    /**
     * Tests that fromSmallestUnit creates an AmountOfMoney with correct amount.
     *
     * @return void
     */
    public function testFromSmallestUnitReturnsCorrectAmount(): void
    {
        $amount = AmountOfMoney::fromSmallestUnit(1050, 'EUR');

        $this->assertEquals(10.50, $amount->getAmount());
    }

    /**
     * Tests that fromSmallestUnit stores the cents value.
     *
     * @return void
     */
    public function testFromSmallestUnitReturnsCorrectAmountInCents(): void
    {
        $amount = AmountOfMoney::fromSmallestUnit(1050, 'EUR');

        $this->assertEquals(1050, $amount->getAmountInCents());
    }

    /**
     * Tests that fromSmallestUnit handles zero amount.
     *
     * @return void
     */
    public function testFromSmallestUnitHandlesZeroAmount(): void
    {
        $amount = AmountOfMoney::fromSmallestUnit(0, 'EUR');

        $this->assertEquals(0.00, $amount->getAmount());
        $this->assertEquals(0, $amount->getAmountInCents());
    }

    // ========== getCurrencyNumeric() tests ==========

    /**
     * Tests that getCurrencyNumeric returns the ISO 4217 numeric code.
     *
     * @return void
     */
    public function testGetCurrencyNumericReturnsIsoCode(): void
    {
        $amount = AmountOfMoney::fromStandardUnit(10, 'EUR');

        $this->assertEquals('978', $amount->getCurrencyNumeric());
    }

    /**
     * Tests getCurrencyNumeric with USD.
     *
     * @return void
     */
    public function testGetCurrencyNumericWithUsd(): void
    {
        $amount = AmountOfMoney::fromStandardUnit(10, 'USD');

        $this->assertEquals('840', $amount->getCurrencyNumeric());
    }

    // ========== formatPrice() tests ==========

    /**
     * Tests that formatPrice returns a formatted string with currency.
     *
     * @return void
     */
    public function testFormatPriceReturnsFormattedString(): void
    {
        $amount = AmountOfMoney::fromStandardUnit(10.50, 'EUR');

        $this->assertEquals('10.50 EUR', $amount->formatPrice());
    }

    /**
     * Tests formatPrice with zero decimal currency like JPY.
     *
     * @return void
     */
    public function testFormatPriceWithZeroDecimalCurrency(): void
    {
        $amount = AmountOfMoney::fromStandardUnit(1000, 'JPY');

        $this->assertEquals('1000 JPY', $amount->formatPrice());
    }

    /**
     * Tests formatPrice with integer amount.
     *
     * @return void
     */
    public function testFormatPriceWithIntegerAmount(): void
    {
        $amount = AmountOfMoney::fromStandardUnit(100, 'EUR');

        $this->assertEquals('100.00 EUR', $amount->formatPrice());
    }

    // ========== compare() tests ==========

    /**
     * Tests that compare returns 0 for equal amounts.
     *
     * @return void
     */
    public function testCompareReturnsZeroForEqualAmounts(): void
    {
        $amount1 = AmountOfMoney::fromStandardUnit(10.50, 'EUR');
        $amount2 = AmountOfMoney::fromStandardUnit(10.50, 'EUR');

        $this->assertEquals(0, $amount1->compare($amount2));
    }

    /**
     * Tests that compare returns positive when first amount is greater.
     *
     * @return void
     */
    public function testCompareReturnsPositiveWhenGreater(): void
    {
        $amount1 = AmountOfMoney::fromStandardUnit(20.00, 'EUR');
        $amount2 = AmountOfMoney::fromStandardUnit(10.50, 'EUR');

        $this->assertEquals(1, $amount1->compare($amount2));
    }

    /**
     * Tests that compare returns negative when first amount is smaller.
     *
     * @return void
     */
    public function testCompareReturnsNegativeWhenSmaller(): void
    {
        $amount1 = AmountOfMoney::fromStandardUnit(5.00, 'EUR');
        $amount2 = AmountOfMoney::fromStandardUnit(10.50, 'EUR');

        $this->assertEquals(-1, $amount1->compare($amount2));
    }

    // ========== sum() tests ==========

    /**
     * Tests that sum correctly adds amounts in standard unit.
     *
     * @return void
     */
    public function testSumAddsAmountsInStandardUnit(): void
    {
        $amounts = [10.00, 20.00, 5.50];
        $total = AmountOfMoney::sum($amounts, 'EUR', false);

        $this->assertEquals(35.50, $total->getAmount());
        $this->assertEquals(3550, $total->getAmountInCents());
    }

    /**
     * Tests that sum correctly adds amounts in smallest unit.
     *
     * @return void
     */
    public function testSumAddsAmountsInSmallestUnit(): void
    {
        $amounts = [1000, 2000, 550];
        $total = AmountOfMoney::sum($amounts, 'EUR', true);

        $this->assertEquals(35.50, $total->getAmount());
        $this->assertEquals(3550, $total->getAmountInCents());
    }

    /**
     * Tests that sum handles empty array.
     *
     * @return void
     */
    public function testSumHandlesEmptyArray(): void
    {
        $total = AmountOfMoney::sum([], 'EUR', false);

        $this->assertEquals(0.00, $total->getAmount());
        $this->assertEquals(0, $total->getAmountInCents());
    }

    /**
     * Tests that sum handles single amount.
     *
     * @return void
     */
    public function testSumHandlesSingleAmount(): void
    {
        $total = AmountOfMoney::sum([10.50], 'EUR', false);

        $this->assertEquals(10.50, $total->getAmount());
    }

    // ========== Currency handling tests ==========

    /**
     * Tests handling of 3-decimal currency (BHD - Bahraini Dinar).
     *
     * @return void
     */
    public function testHandlesThreeDecimalCurrency(): void
    {
        $amount = AmountOfMoney::fromStandardUnit(10.500, 'BHD');

        $this->assertEquals(10.500, $amount->getAmount());
        $this->assertEquals(10500, $amount->getAmountInCents());
    }

    /**
     * Tests that invalid currency code throws exception.
     *
     * @return void
     */
    public function testInvalidCurrencyThrowsException(): void
    {
        $this->expectException(\OutOfBoundsException::class);

        AmountOfMoney::fromStandardUnit(10.50, 'INVALID');
    }

    // ========== subtract() tests ==========

    /**
     * Tests that subtract returns correct difference.
     *
     * @return void
     */
    public function testSubtractReturnsCorrectDifference(): void
    {
        $amount1 = AmountOfMoney::fromStandardUnit(100.00, 'EUR');
        $amount2 = AmountOfMoney::fromStandardUnit(30.00, 'EUR');

        $result = AmountOfMoney::subtract($amount1, $amount2, 'EUR');

        $this->assertEquals(70.00, $result->getAmount());
        $this->assertEquals(7000, $result->getAmountInCents());
    }

    /**
     * Tests that subtract returns correct currency code.
     *
     * @return void
     */
    public function testSubtractReturnsCorrectCurrencyCode(): void
    {
        $amount1 = AmountOfMoney::fromStandardUnit(100.00, 'EUR');
        $amount2 = AmountOfMoney::fromStandardUnit(30.00, 'EUR');

        $result = AmountOfMoney::subtract($amount1, $amount2, 'EUR');

        $this->assertEquals('EUR', $result->getCurrencyCode());
    }

    /**
     * Tests that subtract handles equal amounts.
     *
     * @return void
     */
    public function testSubtractHandlesEqualAmounts(): void
    {
        $amount1 = AmountOfMoney::fromStandardUnit(50.00, 'EUR');
        $amount2 = AmountOfMoney::fromStandardUnit(50.00, 'EUR');

        $result = AmountOfMoney::subtract($amount1, $amount2, 'EUR');

        $this->assertEquals(0.00, $result->getAmount());
        $this->assertEquals(0, $result->getAmountInCents());
    }

    /**
     * Tests that subtract handles decimal amounts.
     *
     * @return void
     */
    public function testSubtractHandlesDecimalAmounts(): void
    {
        $amount1 = AmountOfMoney::fromStandardUnit(100.50, 'EUR');
        $amount2 = AmountOfMoney::fromStandardUnit(30.25, 'EUR');

        $result = AmountOfMoney::subtract($amount1, $amount2, 'EUR');

        $this->assertEquals(70.25, $result->getAmount());
    }

    /**
     * Tests that subtract can return negative amounts.
     *
     * @return void
     */
    public function testSubtractCanReturnNegativeAmount(): void
    {
        $amount1 = AmountOfMoney::fromStandardUnit(30.00, 'EUR');
        $amount2 = AmountOfMoney::fromStandardUnit(100.00, 'EUR');

        $result = AmountOfMoney::subtract($amount1, $amount2, 'EUR');

        $this->assertEquals(-70.00, $result->getAmount());
        $this->assertEquals(-7000, $result->getAmountInCents());
    }

    /**
     * Tests that subtract handles zero decimal currency.
     *
     * @return void
     */
    public function testSubtractHandlesZeroDecimalCurrency(): void
    {
        $amount1 = AmountOfMoney::fromStandardUnit(1000, 'JPY');
        $amount2 = AmountOfMoney::fromStandardUnit(300, 'JPY');

        $result = AmountOfMoney::subtract($amount1, $amount2, 'JPY');

        $this->assertEquals(700, $result->getAmount());
    }

    // ========== convertTo() tests ==========

    /**
     * Tests that convertTo returns correct amount with conversion rate.
     *
     * @return void
     */
    public function testConvertToReturnsCorrectAmount(): void
    {
        $amount = AmountOfMoney::fromStandardUnit(100.00, 'EUR');

        $converted = $amount->convertTo('USD', 1.20);

        $this->assertEquals(120.00, $converted->getAmount());
        $this->assertEquals(12000, $converted->getAmountInCents());
    }

    /**
     * Tests that convertTo returns correct currency code.
     *
     * @return void
     */
    public function testConvertToReturnsCorrectCurrencyCode(): void
    {
        $amount = AmountOfMoney::fromStandardUnit(100.00, 'EUR');

        $converted = $amount->convertTo('USD', 1.20);

        $this->assertEquals('USD', $converted->getCurrencyCode());
    }

    /**
     * Tests that convertTo handles decimal conversion rates.
     *
     * @return void
     */
    public function testConvertToHandlesDecimalConversionRate(): void
    {
        $amount = AmountOfMoney::fromStandardUnit(100.00, 'USD');

        $converted = $amount->convertTo('EUR', 0.85);

        $this->assertEquals(85.00, $converted->getAmount());
    }

    /**
     * Tests that convertTo handles conversion to zero decimal currency.
     *
     * @return void
     */
    public function testConvertToZeroDecimalCurrency(): void
    {
        $amount = AmountOfMoney::fromStandardUnit(10.00, 'EUR');

        $converted = $amount->convertTo('JPY', 130.50);

        $this->assertEquals(1305, $converted->getAmount());
        $this->assertEquals(1305, $converted->getAmountInCents());
    }

    /**
     * Tests that convertTo handles conversion from zero decimal currency.
     *
     * @return void
     */
    public function testConvertToFromZeroDecimalCurrency(): void
    {
        $amount = AmountOfMoney::fromStandardUnit(1000, 'JPY');

        $converted = $amount->convertTo('EUR', 0.0076);

        $this->assertEquals(7.60, $converted->getAmount());
    }

    /**
     * Tests that convertTo with rate 1.0 keeps same amount.
     *
     * @return void
     */
    public function testConvertToWithRateOneKeepsSameAmount(): void
    {
        $amount = AmountOfMoney::fromStandardUnit(50.00, 'EUR');

        $converted = $amount->convertTo('USD', 1.0);

        $this->assertEquals(50.00, $converted->getAmount());
    }

    /**
     * Tests that convertTo rounds correctly.
     *
     * @return void
     */
    public function testConvertToRoundsCorrectly(): void
    {
        $amount = AmountOfMoney::fromStandardUnit(10.00, 'EUR');

        $converted = $amount->convertTo('USD', 1.234567);

        $this->assertEquals(12.35, $converted->getAmount());
    }

    /**
     * Tests that convertTo does not modify original amount.
     *
     * @return void
     */
    public function testConvertToDoesNotModifyOriginal(): void
    {
        $amount = AmountOfMoney::fromStandardUnit(100.00, 'EUR');

        $amount->convertTo('USD', 1.20);

        $this->assertEquals(100.00, $amount->getAmount());
        $this->assertEquals('EUR', $amount->getCurrencyCode());
    }

    /**
     * Tests that convertTo handles small amounts correctly.
     *
     * @return void
     */
    public function testConvertToHandlesSmallAmounts(): void
    {
        $amount = AmountOfMoney::fromStandardUnit(0.01, 'EUR');

        $converted = $amount->convertTo('USD', 1.20);

        $this->assertEquals(0.01, $converted->getAmount());
    }

    /**
     * Tests that convertTo throws exception for invalid target currency.
     *
     * @return void
     */
    public function testConvertToThrowsExceptionForInvalidCurrency(): void
    {
        $this->expectException(\OutOfBoundsException::class);

        $amount = AmountOfMoney::fromStandardUnit(100.00, 'EUR');
        $amount->convertTo('INVALID', 1.0);
    }

    // ========== convertFrom() tests ==========

    /**
     * Tests that convertFrom returns correct amount by dividing.
     *
     * @return void
     */
    public function testConvertFromReturnsCorrectAmount(): void
    {
        $amount = AmountOfMoney::fromStandardUnit(120.00, 'USD');

        $converted = $amount->convertFrom('EUR', 1.20);

        $this->assertEquals(100.00, $converted->getAmount());
        $this->assertEquals(10000, $converted->getAmountInCents());
    }

    /**
     * Tests that convertFrom returns correct currency code.
     *
     * @return void
     */
    public function testConvertFromReturnsCorrectCurrencyCode(): void
    {
        $amount = AmountOfMoney::fromStandardUnit(120.00, 'USD');

        $converted = $amount->convertFrom('EUR', 1.20);

        $this->assertEquals('EUR', $converted->getCurrencyCode());
    }

    /**
     * Tests that convertFrom handles decimal conversion rates.
     *
     * @return void
     */
    public function testConvertFromHandlesDecimalConversionRate(): void
    {
        $amount = AmountOfMoney::fromStandardUnit(85.00, 'EUR');

        $converted = $amount->convertFrom('USD', 0.85);

        $this->assertEquals(100.00, $converted->getAmount());
    }

    /**
     * Tests that convertFrom handles conversion to zero decimal currency.
     *
     * @return void
     */
    public function testConvertFromToZeroDecimalCurrency(): void
    {
        $amount = AmountOfMoney::fromStandardUnit(10.00, 'EUR');

        $converted = $amount->convertFrom('JPY', 0.0076);

        $this->assertEquals(1316, $converted->getAmount());
    }

    /**
     * Tests that convertFrom with rate 1.0 keeps same amount.
     *
     * @return void
     */
    public function testConvertFromWithRateOneKeepsSameAmount(): void
    {
        $amount = AmountOfMoney::fromStandardUnit(50.00, 'EUR');

        $converted = $amount->convertFrom('USD', 1.0);

        $this->assertEquals(50.00, $converted->getAmount());
    }

    /**
     * Tests that convertFrom rounds correctly.
     *
     * @return void
     */
    public function testConvertFromRoundsCorrectly(): void
    {
        $amount = AmountOfMoney::fromStandardUnit(10.00, 'EUR');

        $converted = $amount->convertFrom('USD', 1.234567);

        $this->assertEquals(8.10, $converted->getAmount());
    }

    /**
     * Tests that convertFrom does not modify original amount.
     *
     * @return void
     */
    public function testConvertFromDoesNotModifyOriginal(): void
    {
        $amount = AmountOfMoney::fromStandardUnit(120.00, 'USD');

        $amount->convertFrom('EUR', 1.20);

        $this->assertEquals(120.00, $amount->getAmount());
        $this->assertEquals('USD', $amount->getCurrencyCode());
    }

    /**
     * Tests that convertFrom throws exception for invalid target currency.
     *
     * @return void
     */
    public function testConvertFromThrowsExceptionForInvalidCurrency(): void
    {
        $this->expectException(\OutOfBoundsException::class);

        $amount = AmountOfMoney::fromStandardUnit(100.00, 'EUR');
        $amount->convertFrom('INVALID', 1.0);
    }

    /**
     * Tests that convertTo and convertFrom are inverse operations.
     *
     * @return void
     */
    public function testConvertToAndConvertFromAreInverse(): void
    {
        $rate = 1.20;
        $original = AmountOfMoney::fromStandardUnit(100.00, 'EUR');

        $converted = $original->convertTo('USD', $rate);
        $backToOriginal = $converted->convertFrom('EUR', $rate);

        $this->assertEquals($original->getAmount(), $backToOriginal->getAmount());
    }
}