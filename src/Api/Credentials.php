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

namespace HiPay\PrestaShop\Api;

use HiPay\Fullservice\Gateway\Client\GatewayClient;
use HiPay\Fullservice\HTTP\Configuration\Configuration;
use HiPay\PrestaShop\Settings\Settings;
use Monolog\Logger;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Credentials
 */
class Credentials
{
    const CREDENTIALS_TYPE_MAIN = 'main';
    const CREDENTIALS_TYPE_APPLE_PAY = 'apple_pay';
    const CREDENTIALS_TYPE_MOTO = 'moto';
    const CREDENTIALS_ACCESSIBILITY_PRIVATE = 'private';
    const CREDENTIALS_ACCESSIBILITY_PUBLIC = 'public';

    /** @var Settings */
    private $settings;

    /** @var Logger */
    private $logger;

    /** @var string */
    private $credentialsType;

    /**
     * Credentials Constructor.
     *
     * @param Settings $settings
     * @param Logger   $logger
     * @param string   $credentialsType
     */
    public function __construct(Settings $settings, Logger $logger, string $credentialsType)
    {
        $this->settings = $settings;
        $this->logger = $logger;
        $this->credentialsType = $credentialsType;
    }

    /**
     * @return GatewayClient
     * @throws \Exception
     */
    public function client(): GatewayClient
    {
        $config = new Configuration(
            [
                'apiUsername' => $this->settings->getPublicCredentials($this->credentialsType)->identifiers->username,
                'apiPassword' => $this->settings->getPublicCredentials($this->credentialsType)->identifiers->password,
                'apiEnv' => $this->settings->getPublicCredentials($this->credentialsType)->env,
                'hostedPageV2' => true
            ]
        );
        $clientProvider = new HiPayPaymentsHTTPClient($config, $this->logger);

        return new GatewayClient($clientProvider);
    }

    /**
     * @return GatewayClient
     * @throws \Exception
     */
    public function server(): GatewayClient
    {
        $config = new Configuration(
            [
                'apiUsername' => $this->settings->getPrivateCredentials($this->credentialsType)->identifiers->username,
                'apiPassword' => $this->settings->getPrivateCredentials($this->credentialsType)->identifiers->password,
                'apiEnv' => $this->settings->getPrivateCredentials($this->credentialsType)->env,
                'hostedPageV2' => true
            ]
        );
        $clientProvider = new HiPayPaymentsHTTPClient($config, $this->logger);

        return new GatewayClient($clientProvider);
    }
}
