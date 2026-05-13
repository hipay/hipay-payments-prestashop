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

namespace HiPay\PrestaShop\Presenter;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class TransactionPresented
 */
class TransactionPresented
{
    /** @var bool */
    public $validateOrder = false;

    /** @var bool */
    public $updateStatus = false;

    /** @var mixed[] */
    public $validation = [
        'idCart' => 0,
        'idOrderState' => 0,
        'idShop' => 0,
        'transactionReference' => '',
        'amount' => 0,
        'paymentMethod' => '',
        'secureKey' => '',
    ];

    /** @var int */
    public $orderId;

    /** @var mixed[] */
    public $transaction = [
        'idCart' => 0,
        'transactionReference' => '',
    ];

    /** @var int */
    public $newStatus;

    /** @var bool */
    public $saveMotoTransaction = false;
}
