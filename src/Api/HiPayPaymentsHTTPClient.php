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

use HiPay\Fullservice\Exception\ApiErrorException;
use HiPay\Fullservice\Exception\CurlException;
use HiPay\Fullservice\Exception\HttpErrorException;
use HiPay\Fullservice\Exception\InvalidArgumentException;
use HiPay\Fullservice\Gateway\Client\GatewayClient;
use HiPay\Fullservice\HTTP\ClientProvider;
use HiPay\Fullservice\HTTP\Configuration\ConfigurationInterface;
use HiPay\Fullservice\HTTP\Response\AbstractResponse;
use HiPay\Fullservice\HTTP\Response\Response;
use Monolog\Logger;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class HiPayPaymentsHTTPClient
 */
class HiPayPaymentsHTTPClient extends ClientProvider
{
    /** @var Logger */
    private $logger;

    /**
     * HiPayPaymentsHTTPClient Constructor.
     *
     * @param ConfigurationInterface $configuration
     * @param Logger                 $logger
     * @throws \Exception
     */
    public function __construct(ConfigurationInterface $configuration, Logger $logger)
    {
        $this->logger = $logger;
        parent::__construct($configuration);
    }

    /**
     * {@inheritDoc}
     *
     * @see \HiPay\Fullservice\HTTP\ClientProvider::doRequest()
     *
     * @param string $method
     * @param string $endpoint
     * @param array<string, mixed> $params
     * @param array<int, string> $additionalHeaders
     * @param bool $isVault
     * @param bool $isData
     * @return AbstractResponse
     */
    protected function doRequest($method, $endpoint, array $params = array(), array $additionalHeaders = array(), $isVault = false, $isData = false)
    {
        if (empty($method) || !is_string($method)) {
            throw new InvalidArgumentException("HTTP METHOD must a string and a valid HTTP METHOD Value");
        } elseif (!$this->validateHttpMethod($method)) {
            throw new InvalidArgumentException("HTTP METHOD \"$method\" doesn't exist");
        }

        if (empty($endpoint) || !is_string($endpoint)) {
            throw new InvalidArgumentException("Endpoint must be a string and a valid api endpoint");
        }

        $credentials = $this->getConfiguration()->getApiUsername() . ':' . $this->getConfiguration()->getApiPassword();

        if ($endpoint === GatewayClient::ENDPOINT_HOSTED_PAYMENT_PAGE) {
            $url = $this->getConfiguration()->getApiEndpointV2();
        } else {
            $url = $this->getConfiguration()->getApiEndpoint();
        }

        $timeout = $this->getConfiguration()->getCurlTimeout();
        $connectTimeout = $this->getConfiguration()->getCurlConnectTimeout();

        if ($isVault) {
            $url = $this->getConfiguration()->getSecureVaultEndpoint();
        }


        $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'HiPayFullservice/1.0 (SDK PHP)';

        // Handling data API configuration
        if ($isData) {
            $url = $this->getConfiguration()->getDataApiEndpoint();
            $timeout = 3;
            $connectTimeout = 3;
            $userAgent = $this->getConfiguration()->getDataApiHttpUserAgent();
        }

        $finalUrl = $url . $endpoint;

        // set appropriate options
        $options = array(
            CURLOPT_URL => $finalUrl,
            CURLOPT_USERPWD => $credentials,
            CURLOPT_HTTPHEADER => array_merge($additionalHeaders, array(
                'Accept: ' . $this->getConfiguration()->getApiHTTPHeaderAccept(),
                'User-Agent: ' . $userAgent
            )),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FAILONERROR => false,
            CURLOPT_HEADER => false,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => $connectTimeout,
        );

        if ($isData) {
            $options[CURLOPT_HTTPHEADER][] = 'X-Who-Api: ' . $this->getConfiguration()->getDataApiHttpUserAgent();
            unset($options[CURLOPT_USERPWD]);
        }

        // add post parameters
        if (strtolower($method) == 'post') {
            $options[CURLOPT_POST] = true;
            if ($isData) {
                $options[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
                $options[CURLOPT_POSTFIELDS] = json_encode($params);
            } else {
                $options[CURLOPT_POSTFIELDS] = http_build_query($params);
            }
        } else {
            $options[CURLOPT_POST] = false;
        }

        $proxyConfiguration = $this->getConfiguration()->getProxy();

        if (!empty($proxyConfiguration)) {
            $options[CURLOPT_PROXY] = $proxyConfiguration["host"];
            $options[CURLOPT_PROXYPORT] = $proxyConfiguration["port"];
            $options[CURLOPT_PROXYUSERPWD] = $proxyConfiguration["user"] . ":" . $proxyConfiguration["password"];
        }

        $obfuscatedParams = $params;
        array_walk_recursive($obfuscatedParams, [$this, 'obfuscateParams']);
        $this->logger->debug(sprintf('Request %s %s', $method, $endpoint), [
            'request' => $obfuscatedParams,
            'uri' => $finalUrl,
        ]);

        /**
         * Send a new request
         * $method can be any valid HTTP METHOD (GET, POST etc ...)
         * $uri The url/endpoint to request
         * $options Needed configuration
         */
        foreach ($options as $option => $value) {
            curl_setopt($this->_httpClient, $option, $value);
        }

        /**
         * @var string|false $result
         */
        $result = curl_exec($this->_httpClient);

        // execute the given cURL session
        if (($result === false) && !$isData) {
            throw new CurlException(curl_error($this->_httpClient), curl_errno($this->_httpClient));
        }

        $status = (int)curl_getinfo($this->_httpClient, CURLINFO_HTTP_CODE);

        if (floor($status / 100) != 2 && !$isData) {
            $httpResponse = json_decode($result);

            if (is_object($httpResponse) && isset($httpResponse->message, $httpResponse->code)) {
                $description = (isset($httpResponse->description)) ? $httpResponse->description : "";
                $obfuscatedResult = json_decode((string) $result, true);
                array_walk_recursive($obfuscatedResult, [$this, 'obfuscateParams']);
                $this->logger->debug(sprintf('Response (HTTP %d) %s %s', $httpResponse->code, $method, $endpoint), [
                    'message' => $httpResponse->message,
                    'description' => $description,
                    'result' => $obfuscatedResult,
                ]);
                throw new ApiErrorException($httpResponse->message, $httpResponse->code, $description);
            } else {
                $obfuscatedResult = json_decode((string) $result, true);
                array_walk_recursive($obfuscatedResult, [$this, 'obfuscateParams']);
                $this->logger->debug(sprintf('Response (HTTP %d) %s %s', $status, $method, $endpoint), [
                    'result' => $obfuscatedResult,
                ]);
                throw new HttpErrorException($result, $status);
            }
        }

        $obfuscatedResult = json_decode((string) $result, true);
        if (true !== $obfuscatedResult) {
            array_walk_recursive($obfuscatedResult, [$this, 'obfuscateParams']);
        }
        $this->logger->debug(sprintf('Response (HTTP %d) %s %s', $status, $method, $endpoint), [
            'response' => $obfuscatedResult,
        ]);

        //Return a simple response object
        return new Response((string)$result, $status, array('Content-Type' => array('application/json; encoding=UTF-8')));
    }

    /**
     * {@inheritDoc}
     *
     * @see \HiPay\Fullservice\HTTP\ClientProvider::createHttpClient()
     *
     * @return void
     */
    protected function createHttpClient()
    {
        $this->_httpClient = curl_init();
    }

    /**
     * @param mixed  $item
     * @param string $key
     * @return void
     */
    private function obfuscateParams(&$item, string $key)
    {
        $toObfuscate = [
            'firstname',
            'lastname',
            'country',
            'city',
            'email',
            'phone',
            'recipientinfo',
            'streetaddress',
            'streetaddress2',
            'zipcode',
            'shipto_firstname',
            'shipto_lastname',
            'shipto_country',
            'shipto_city',
            'shipto_phone',
            'shipto_recipientinfo',
            'shipto_streetaddress',
            'shipto_streetaddress2',
            'shipto_zipcode',
            'cardtoken',
        ];
        if (in_array($key, $toObfuscate, true)) {
            $item = '***';
        }
        $toDecode = [
            'delivery_method',
            'source',
            'basket',
        ];
        if (in_array($key, $toDecode, true)) {
            $item = json_decode($item, true);
        }
    }
}
