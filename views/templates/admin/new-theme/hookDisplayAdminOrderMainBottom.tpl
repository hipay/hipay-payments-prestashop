{**
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
 *
 *}

<div class="card mt-2 hipay-admin-order" id="js-hipay-admin-order">
    <div class="card-header">
        <h3 class="card-header-title">
            <img src="/modules/hipaypayments/logo.png" alt="logo" />
            HiPay Payments
        </h3>
    </div>
    <div class="card-body" style="position: relative;">
        <div id="js-hipay-generic-message" class="hidden">
            <div class="alert alert-danger">
                <p>
                    {l s='An unexpected error occurred. Please try again.' mod='hipaypayments'}
                </p>
            </div>
        </div>
        <div id="js-hipay-transaction-content" class="hipay-transaction-content"></div>
        <div class="hipay-overlay hidden" id="js-hipay-overlay">
            <img src="/modules/hipaypayments/views/img/icons/loader.svg" />
        </div>
    </div>
</div>
{include file="./_partials/modalOperations.tpl"}
