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

namespace HiPay\PrestaShop\Settings\Serializer;

use Symfony\Component\Serializer\Encoder\JsonEncoder;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class SettingsJsonEncoder
 */
class SettingsJsonEncoder extends JsonEncoder
{
    /**
     * @param mixed[] $data
     * @param string  $format
     * @param mixed[] $context
     * @return string
     */
    public function encode($data, $format, array $context = []): string
    {
        unset($data['extra']);

        return (string) parent::encode($data, $format, $context);
    }
}
