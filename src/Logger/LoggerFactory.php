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

namespace HiPay\PrestaShop\Logger;

use AG\PSModuleUtils\Logger\AbstractLoggerFactory;
use HiPay\PrestaShop\Settings\Settings;
use Monolog\Logger;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class LoggerFactory
 */
class LoggerFactory extends AbstractLoggerFactory
{
    /** @var Settings */
    private $settings;

    /**
     * LoggerFactory Constructor.
     *
     * @param Settings $settings
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
        parent::__construct();
    }

    /**
     * @return int
     */
    public function getLoggerLevel(): int
    {
        return $this->settings->mainSettings->verboseLogsEnabled ? Logger::DEBUG : Logger::INFO;
    }

    /**
     * @return string
     */
    public function getLogFilePath(): string
    {
        return _PS_MODULE_DIR_.'/hipaypayments/logs/';
    }

    /**
     * @param Settings $settings
     * @return LoggerFactory
     */
    public function withSettings(Settings $settings): LoggerFactory
    {
        $this->settings = $settings;

        return new self($settings);
    }
}
