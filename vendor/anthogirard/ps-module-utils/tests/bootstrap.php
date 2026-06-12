<?php

/**
 * Bootstrap file for PHPUnit tests.
 * Defines PrestaShop functions and constants needed for testing.
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Define PrestaShop constants for testing
if (!defined('_COOKIE_IV_')) {
    define('_COOKIE_IV_', 'test_cookie_iv_');
}

if (!defined('_PS_USE_SQL_SLAVE_')) {
    define('_PS_USE_SQL_SLAVE_', false);
}

// Mock PrestaShop Tools class if not already defined
if (!class_exists('Tools', false)) {
    /**
     * Mock PrestaShop Tools class for unit testing.
     */
    class Tools
    {
        /**
         * @param float|int $value
         * @param int       $precision
         * @return float
         */
        public static function ps_round($value, int $precision = 0): float
        {
            return round((float) $value, $precision);
        }
    }
}

// Mock PrestaShop Shop class
if (!class_exists('Shop', false)) {
    /**
     * Mock PrestaShop Shop class for unit testing.
     */
    class Shop
    {
        /** @var int */
        public $id = 1;
    }
}

// Mock PrestaShop Context class
if (!class_exists('Context', false)) {
    /**
     * Mock PrestaShop Context class for unit testing.
     */
    class Context
    {
        /** @var Context|null */
        private static $instance;

        /** @var Shop */
        public $shop;

        /**
         * Context constructor.
         */
        public function __construct()
        {
            $this->shop = new Shop();
        }

        /**
         * @return Context
         */
        public static function getContext(): Context
        {
            if (null === self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         * @param Context $context
         * @return void
         */
        public static function setContext(Context $context): void
        {
            self::$instance = $context;
        }

        /**
         * @return void
         */
        public static function resetContext(): void
        {
            self::$instance = null;
        }
    }
}

// Mock PrestaShop DbQuery class
if (!class_exists('DbQuery', false)) {
    /**
     * Mock PrestaShop DbQuery class for unit testing.
     */
    class DbQuery
    {
        /** @var string */
        private $query = '';

        /**
         * @param string $fields
         * @return $this
         */
        public function select(string $fields): self
        {
            $this->query .= "SELECT $fields ";
            return $this;
        }

        /**
         * @param string      $table
         * @param string|null $alias
         * @return $this
         */
        public function from(string $table, ?string $alias = null): self
        {
            $this->query .= "FROM $table" . ($alias ? " $alias " : ' ');
            return $this;
        }

        /**
         * @param string      $table
         * @param string|null $alias
         * @param string|null $on
         * @return $this
         */
        public function leftJoin(string $table, ?string $alias = null, ?string $on = null): self
        {
            $this->query .= "LEFT JOIN $table" . ($alias ? " $alias" : '') . ($on ? " ON $on " : ' ');
            return $this;
        }

        /**
         * @param string $restriction
         * @return $this
         */
        public function where(string $restriction): self
        {
            $this->query .= "WHERE $restriction ";
            return $this;
        }

        /**
         * @param string $fields
         * @return $this
         */
        public function orderBy(string $fields): self
        {
            $this->query .= "ORDER BY $fields ";
            return $this;
        }

        /**
         * @return string
         */
        public function __toString(): string
        {
            return $this->query;
        }
    }
}

// Mock PrestaShop Db class
if (!class_exists('Db', false)) {
    /**
     * Mock PrestaShop Db class for unit testing.
     */
    class Db
    {
        /** @var Db|null */
        private static $instance;

        /** @var mixed[]|null */
        private static $mockExecuteSResult;

        /** @var mixed */
        private static $mockGetValueResult;

        /**
         * @param bool $slave
         * @return Db
         */
        public static function getInstance(bool $slave = false): Db
        {
            if (null === self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         * @param mixed[]|false|null $result
         * @return void
         */
        public static function setMockExecuteSResult($result): void
        {
            self::$mockExecuteSResult = $result;
        }

        /**
         * @param mixed $result
         * @return void
         */
        public static function setMockGetValueResult($result): void
        {
            self::$mockGetValueResult = $result;
        }

        /**
         * @return void
         */
        public static function resetMock(): void
        {
            self::$mockExecuteSResult = null;
            self::$mockGetValueResult = null;
        }

        /**
         * @param DbQuery|string $query
         * @return mixed[]|false|null
         */
        public function executeS($query)
        {
            return self::$mockExecuteSResult;
        }

        /**
         * @param DbQuery|string $query
         * @return mixed
         */
        public function getValue($query)
        {
            return self::$mockGetValueResult ?? false;
        }
    }
}

// Mock PrestaShop Country class
if (!class_exists('Country', false)) {
    /**
     * Mock PrestaShop Country class for unit testing.
     */
    class Country
    {
        /** @var mixed[]|null */
        private static $mockCountries;

        /**
         * @param mixed[]|null $countries
         * @return void
         */
        public static function setMockCountries(?array $countries): void
        {
            self::$mockCountries = $countries;
        }

        /**
         * @return void
         */
        public static function resetMock(): void
        {
            self::$mockCountries = null;
        }

        /**
         * @param int  $idLang
         * @param bool $active
         * @param bool $containsStates
         * @param bool $listStates
         * @return mixed[]
         */
        public static function getCountries(int $idLang, bool $active = false, bool $containsStates = false, bool $listStates = true): array
        {
            return self::$mockCountries ?? [];
        }
    }
}

// Mock PrestaShop Order class
if (!class_exists('Order', false)) {
    /**
     * Mock PrestaShop Order class for unit testing.
     */
    class Order
    {
        /** @var int */
        public $id;

        /** @var int */
        public $id_cart;

        /**
         * @param int|null $id
         */
        public function __construct(?int $id = null)
        {
            $this->id = $id ?? 0;
        }
    }
}