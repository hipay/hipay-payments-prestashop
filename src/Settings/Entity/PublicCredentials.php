<?php
/**
 * 2026 HiPay
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0).
 * It is also available through the world-wide-web at this URL: https://opensource.org/licenses/AFL-3.0
 *
 * @author    HiPay partner
 * @copyright 2026
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

namespace HiPay\PrestaShop\Settings\Entity;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PublicCredentials
{
    /** @var PublicIdentifiers */
    public $identifiers;

    /** @var string */
    public $env;

    /** @var string */
    public $hashingAlgorithm;

}
