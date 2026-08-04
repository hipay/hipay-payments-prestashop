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

namespace HiPay\PrestaShop\Settings\Entity;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class MainSettings
 */
class MainSettings
{
    const CAPTURE_MODE_AUTO = 'auto';
    const CAPTURE_MODE_MANUAL = 'manual';
    const OPERATION_VALUE = [
        'auto' => 'Sale',
        'manual' => 'Authorization',
    ];

    const POSITION_ABOVE = 'above';
    const POSITION_BELOW = 'below';
    const DEFAULT_HOSTED_PAGE_LABEL = 'Secured Payments';

    /** @var string */
    public $captureMode;

    /** @var bool */
    public $verboseLogsEnabled;

    /** @var bool */
    public $hostedPageEnabled;

    /** @var string */
    public $hostedPageType;

    /** @var bool */
    public $cancelButtonDisplayed;

    /** @var string */
    public $threeDSMode;

    /** @var string[] */
    public $hostedPageLabel = [];

    /** @var string */
    public $hostedPagePosition = self::POSITION_ABOVE;
}
