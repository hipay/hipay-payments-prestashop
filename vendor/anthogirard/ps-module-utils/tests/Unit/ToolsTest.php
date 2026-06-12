<?php

namespace Tests\AG\PSModuleUtils;

use AG\PSModuleUtils\Tools;
use PHPUnit\Framework\TestCase;

/**
 * Class ToolsTest
 * @package Tests\AG\PSModuleUtils
 */
class ToolsTest extends TestCase
{
    /** @var string */
    private $tempDir;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = sys_get_temp_dir() . '/tools_test_' . uniqid();
        mkdir($this->tempDir, 0777, true);
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        if (is_dir($this->tempDir)) {
            $this->removeDirectory($this->tempDir);
        }
        // Reset mocks
        \Db::resetMock();
        \Country::resetMock();
        \Context::resetContext();
    }

    /**
     * @param string $dir
     * @return void
     */
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }


    /**
     * Tests that hash returns a valid md5 hash.
     *
     * @return void
     */
    public function testHashReturnsValidMd5(): void
    {
        $result = Tools::hash('test_value');
        $this->assertRegExp('/^[a-f0-9]{32}$/', $result);
    }

    /**
     * Tests that hash returns consistent results for the same input.
     *
     * @return void
     */
    public function testHashIsConsistent(): void
    {
        $value = 'consistent_test';
        $hash1 = Tools::hash($value);
        $hash2 = Tools::hash($value);
        $this->assertEquals($hash1, $hash2);
    }

    /**
     * Tests that hash returns different results for different inputs.
     *
     * @return void
     */
    public function testHashReturnsDifferentResultsForDifferentInputs(): void
    {
        $hash1 = Tools::hash('value1');
        $hash2 = Tools::hash('value2');
        $this->assertNotEquals($hash1, $hash2);
    }

    /**
     * Tests that hash uses the cookie IV in the hash.
     *
     * @return void
     */
    public function testHashUsesCookieIv(): void
    {
        $value = 'test';
        $expectedHash = md5(_COOKIE_IV_ . $value);
        $this->assertEquals($expectedHash, Tools::hash($value));
    }

    // ========== copy() tests ==========

    /**
     * Tests that copy successfully copies a file.
     *
     * @return void
     */
    public function testCopyCopiesFile(): void
    {
        $source = $this->tempDir . '/source.txt';
        $destination = $this->tempDir . '/destination.txt';
        file_put_contents($source, 'test content');

        Tools::copy($source, $destination);

        $this->assertFileExists($destination);
        $this->assertEquals('test content', file_get_contents($destination));
    }

    /**
     * Tests that copy overwrites existing destination file.
     *
     * @return void
     */
    public function testCopyOverwritesExistingFile(): void
    {
        $source = $this->tempDir . '/source.txt';
        $destination = $this->tempDir . '/destination.txt';
        file_put_contents($source, 'new content');
        file_put_contents($destination, 'old content');

        Tools::copy($source, $destination);

        $this->assertEquals('new content', file_get_contents($destination));
    }

    /**
     * Tests that copy preserves file content exactly.
     *
     * @return void
     */
    public function testCopyPreservesContent(): void
    {
        $source = $this->tempDir . '/source.txt';
        $destination = $this->tempDir . '/destination.txt';
        $content = "Line 1\nLine 2\nSpecial chars: éàü";
        file_put_contents($source, $content);

        Tools::copy($source, $destination);

        $this->assertEquals($content, file_get_contents($destination));
    }

    // ========== getServerHttpHeaders() tests ==========

    /**
     * Tests that getServerHttpHeaders returns HTTP headers from $_SERVER.
     *
     * @return void
     */
    public function testGetServerHttpHeadersReturnsHttpHeaders(): void
    {
        $originalServer = $_SERVER;
        $_SERVER = [
            'HTTP_HOST' => 'example.com',
            'HTTP_USER_AGENT' => 'TestAgent/1.0',
            'HTTP_ACCEPT' => 'text/html',
        ];

        $headers = Tools::getServerHttpHeaders();

        $this->assertArrayHasKey('Host', $headers);
        $this->assertArrayHasKey('User-Agent', $headers);
        $this->assertArrayHasKey('Accept', $headers);
        $this->assertEquals('example.com', $headers['Host']);
        $this->assertEquals('TestAgent/1.0', $headers['User-Agent']);

        $_SERVER = $originalServer;
    }

    /**
     * Tests that getServerHttpHeaders ignores non-HTTP keys.
     *
     * @return void
     */
    public function testGetServerHttpHeadersIgnoresNonHttpKeys(): void
    {
        $originalServer = $_SERVER;
        $_SERVER = [
            'HTTP_HOST' => 'example.com',
            'REQUEST_METHOD' => 'GET',
            'SERVER_NAME' => 'localhost',
            'DOCUMENT_ROOT' => '/var/www',
        ];

        $headers = Tools::getServerHttpHeaders();

        $this->assertCount(1, $headers);
        $this->assertArrayHasKey('Host', $headers);
        $this->assertArrayNotHasKey('REQUEST_METHOD', $headers);

        $_SERVER = $originalServer;
    }

    /**
     * Tests that getServerHttpHeaders returns empty array when no HTTP headers.
     *
     * @return void
     */
    public function testGetServerHttpHeadersReturnsEmptyArrayWhenNoHttpHeaders(): void
    {
        $originalServer = $_SERVER;
        $_SERVER = [
            'REQUEST_METHOD' => 'GET',
            'SERVER_NAME' => 'localhost',
        ];

        $headers = Tools::getServerHttpHeaders();

        $this->assertIsArray($headers);
        $this->assertEmpty($headers);

        $_SERVER = $originalServer;
    }

    /**
     * Tests that getServerHttpHeaders formats header names correctly.
     *
     * @return void
     */
    public function testGetServerHttpHeadersFormatsHeaderNames(): void
    {
        $originalServer = $_SERVER;
        $_SERVER = [
            'HTTP_CONTENT_TYPE' => 'application/json',
            'HTTP_X_CUSTOM_HEADER' => 'custom_value',
        ];

        $headers = Tools::getServerHttpHeaders();

        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertArrayHasKey('X-Custom-Header', $headers);

        $_SERVER = $originalServer;
    }
    /**
     * Tests that the generateRandomString method creates a string of the specified length.
     */
    public function testGenerateRandomStringReturnsCorrectLength()
    {
        $length = 10;
        $generatedString = Tools::generateRandomString($length);
        $this->assertEquals($length, strlen($generatedString), 'Generated string length does not match the specified length.');
    }

    /**
     * Tests that the generateRandomString method creates a string containing only the allowed characters.
     */
    public function testGenerateRandomStringUsesAllowedCharacters()
    {
        $length = 20;
        $allowedChars = Tools::RANDOM_STRING_CHARS;
        $generatedString = Tools::generateRandomString($length);

        foreach (str_split($generatedString) as $char) {
            $this->assertStringContainsString($char, $allowedChars, 'Generated string contains characters outside the allowed set.');
        }
    }

    /**
     * Tests that the generateRandomString method generates strings of varying content for multiple calls.
     */
    public function testGenerateRandomStringIsRandomized()
    {
        $length = 15;
        $firstString = Tools::generateRandomString($length);
        $secondString = Tools::generateRandomString($length);

        $this->assertNotEquals($firstString, $secondString, 'Generated strings are not unique across multiple calls.');
    }

    /**
     * Tests that the generateRandomString method handles a default parameter correctly.
     */
    public function testGenerateRandomStringUsesDefaultLength()
    {
        $defaultLength = 7;
        $generatedString = Tools::generateRandomString();
        $this->assertEquals($defaultLength, strlen($generatedString), 'Generated string does not use the default length when no argument is provided.');
    }

    /**
     * Tests that the generateRandomString method handles zero length appropriately.
     */
    public function testGenerateRandomStringHandlesZeroLength()
    {
        $generatedString = Tools::generateRandomString(0);
        $this->assertEquals('', $generatedString, 'Generated string is not empty when length is zero.');
    }

    /**
     * Tests that the generateRandomString method throws an error for negative lengths.
     */
    public function testGenerateRandomStringThrowsErrorForNegativeLength()
    {
        $this->expectException(\InvalidArgumentException::class);
        Tools::generateRandomString(-5);
    }

    // ========== getPaymentCurrencies() tests ==========

    /**
     * Tests that getPaymentCurrencies returns currencies from database.
     *
     * @return void
     * @throws \PrestaShopDatabaseException
     */
    public function testGetPaymentCurrenciesReturnsCurrencies(): void
    {
        $mockCurrencies = [
            ['id_currency' => 1, 'iso_code' => 'EUR', 'name' => 'Euro'],
            ['id_currency' => 2, 'iso_code' => 'USD', 'name' => 'US Dollar'],
        ];
        \Db::setMockExecuteSResult($mockCurrencies);

        $result = Tools::getPaymentCurrencies(1, 1, 1);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('EUR', $result[0]['iso_code']);
        $this->assertEquals('USD', $result[1]['iso_code']);
    }

    /**
     * Tests that getPaymentCurrencies returns empty array when no currencies found.
     *
     * @return void
     * @throws \PrestaShopDatabaseException
     */
    public function testGetPaymentCurrenciesReturnsEmptyArrayWhenNoCurrencies(): void
    {
        \Db::setMockExecuteSResult([]);

        $result = Tools::getPaymentCurrencies(1, 1, 1);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Tests that getPaymentCurrencies uses context shop id when not provided.
     *
     * @return void
     * @throws \PrestaShopDatabaseException
     */
    public function testGetPaymentCurrenciesUsesContextShopId(): void
    {
        $context = \Context::getContext();
        $context->shop->id = 5;

        $mockCurrencies = [
            ['id_currency' => 1, 'iso_code' => 'EUR', 'name' => 'Euro'],
        ];
        \Db::setMockExecuteSResult($mockCurrencies);

        $result = Tools::getPaymentCurrencies(1, 1);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
    }

    /**
     * Tests that getPaymentCurrencies handles empty result set.
     *
     * @return void
     * @throws \PrestaShopDatabaseException
     */
    public function testGetPaymentCurrenciesHandlesEmptyResultSet(): void
    {
        \Db::setMockExecuteSResult([]);

        $result = Tools::getPaymentCurrencies(1, 1, 1);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    // ========== getPaymentCountries() tests ==========

    /**
     * Tests that getPaymentCountries returns filtered countries.
     *
     * @return void
     * @throws \PrestaShopDatabaseException
     */
    public function testGetPaymentCountriesReturnsFilteredCountries(): void
    {
        $enabledCountries = [
            ['id_country' => 1, 'name' => 'France'],
            ['id_country' => 2, 'name' => 'Germany'],
            ['id_country' => 3, 'name' => 'Spain'],
        ];
        \Country::setMockCountries($enabledCountries);

        $moduleCountries = [
            ['id_country' => 1],
            ['id_country' => 3],
        ];
        \Db::setMockExecuteSResult($moduleCountries);

        $result = Tools::getPaymentCountries(1, 1, 1);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    /**
     * Tests that getPaymentCountries returns empty array when no enabled countries.
     *
     * @return void
     * @throws \PrestaShopDatabaseException
     */
    public function testGetPaymentCountriesReturnsEmptyArrayWhenNoEnabledCountries(): void
    {
        \Country::setMockCountries([]);

        $result = Tools::getPaymentCountries(1, 1, 1);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Tests that getPaymentCountries returns empty array when no module countries.
     *
     * @return void
     * @throws \PrestaShopDatabaseException
     */
    public function testGetPaymentCountriesReturnsEmptyArrayWhenNoModuleCountries(): void
    {
        $enabledCountries = [
            ['id_country' => 1, 'name' => 'France'],
        ];
        \Country::setMockCountries($enabledCountries);
        \Db::setMockExecuteSResult(null);

        $result = Tools::getPaymentCountries(1, 1, 1);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Tests that getPaymentCountries uses context shop id when not provided.
     *
     * @return void
     * @throws \PrestaShopDatabaseException
     */
    public function testGetPaymentCountriesUsesContextShopId(): void
    {
        $context = \Context::getContext();
        $context->shop->id = 3;

        $enabledCountries = [
            ['id_country' => 1, 'name' => 'France'],
        ];
        \Country::setMockCountries($enabledCountries);

        $moduleCountries = [
            ['id_country' => 1],
        ];
        \Db::setMockExecuteSResult($moduleCountries);

        $result = Tools::getPaymentCountries(1, 1);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
    }

    /**
     * Tests that getPaymentCountries filters out countries not in module list.
     *
     * @return void
     * @throws \PrestaShopDatabaseException
     */
    public function testGetPaymentCountriesFiltersCorrectly(): void
    {
        $enabledCountries = [
            ['id_country' => 1, 'name' => 'France'],
            ['id_country' => 2, 'name' => 'Germany'],
        ];
        \Country::setMockCountries($enabledCountries);

        $moduleCountries = [
            ['id_country' => 2],
        ];
        \Db::setMockExecuteSResult($moduleCountries);

        $result = Tools::getPaymentCountries(1, 1, 1);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('Germany', reset($result)['name']);
    }

    // ========== getOrderByCartId() tests ==========

    /**
     * Tests that getOrderByCartId returns an Order object.
     *
     * @return void
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function testGetOrderByCartIdReturnsOrder(): void
    {
        \Db::setMockGetValueResult(123);

        $result = Tools::getOrderByCartId(456);

        $this->assertInstanceOf(\Order::class, $result);
        $this->assertEquals(123, $result->id);
    }

    /**
     * Tests that getOrderByCartId returns Order with id 0 when not found.
     *
     * @return void
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function testGetOrderByCartIdReturnsOrderWithZeroIdWhenNotFound(): void
    {
        \Db::setMockGetValueResult(false);

        $result = Tools::getOrderByCartId(999);

        $this->assertInstanceOf(\Order::class, $result);
        $this->assertEquals(0, $result->id);
    }

    /**
     * Tests that getOrderByCartId handles string id from database.
     *
     * @return void
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function testGetOrderByCartIdHandlesStringId(): void
    {
        \Db::setMockGetValueResult('789');

        $result = Tools::getOrderByCartId(100);

        $this->assertInstanceOf(\Order::class, $result);
        $this->assertEquals(789, $result->id);
    }
}