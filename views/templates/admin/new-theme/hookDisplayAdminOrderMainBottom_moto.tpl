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
            <img src="/modules/hipaypayments/logo.png" alt="logo">
            HiPay Payments
        </h3>
    </div>
    <div class="card-body">
        {if isset($hiPayMotoError)}
            <div class="alert alert-danger">
                {$hiPayMotoError|escape:'html':'UTF-8'}
            </div>
        {else}
            {if !$motoAccountConfigured}
                <div class="alert alert-info">
                    <p>
                        {l s='MOTO credentials are not configured. Therefore, the main account credentials will be used to create the payment.' mod='hipaypayments'}
                    </p>
                </div>
            {/if}
            <div class="alert alert-info">
                <p>
                    {l s='You will be redirected to the HiPay payment page to complete the payment of this order.' mod='hipaypayments'}
                </p>
            </div>
            <a class="btn btn-primary"
               href="{$hiPayPaymentLinkMoto|escape:'html':'UTF-8'}"
            >
                {l s='Go to payment page' mod='hipaypayments'}
            </a>
        {/if}
    </div>
</div>
