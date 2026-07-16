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

{extends file='page.tpl'}

{block name="head"}
    {$smarty.block.parent}
    <script>
      const hipayRedirectController = "{$hipayRedirectController|escape:'javascript':'UTF-8'|replace:'&amp;':'&' nofilter}";
      const hipayPaymentControllerUrl = "{$hipayPaymentControllerUrl|escape:'javascript':'UTF-8'|replace:'&amp;':'&' nofilter}";
      const hipayCustomerToken = "{$hipayCustomerToken|escape:'javascript':'UTF-8'}";
      const hipayOrderId = "{$hipayOrderId|escape:'javascript':'UTF-8'}";
      const hipayTransactionReference = "{$hipayOrderId|escape:'javascript':'UTF-8'}";
      const idCart = "{$idCart|escape:'javascript':'UTF-8'}";
      const cartSecureKey = "{$cartSecureKey|escape:'javascript':'UTF-8'}";
      const paymentProduct = "{$paymentProduct|escape:'javascript':'UTF-8'}";
    </script>
{/block}

{block name='page_content_container'}
    {if $paymentProduct === 'bancomatpay' || $paymentProduct === 'bizum'}
        <div id="js-hipay-bancomat-status">
            <div class="js-hipay-bancomat-state js-hipay-bancomat-state--pending">
                <img id="js-hipay-loader" src="{$smarty.const.BASE_URL|escape:'htmlall':'UTF-8'}/modules/hipaypayments/views/img/icons/loader.svg" alt="" />
                <h2>{l s='Payment pending' mod='hipaypayments'}</h2>
                {if $paymentProduct === 'bizum'}
                    <p>{l s='The payment will need to be validated on your Bizum application.' mod='hipaypayments'}</p>
                {else}
                    <p>{l s='The payment will need to be validated on your Bancomat Pay application.' mod='hipaypayments'}</p>
                {/if}
                <p>{l s='This page will update automatically once the payment is confirmed.' mod='hipaypayments'}</p>
            </div>
            <div class="js-hipay-bancomat-state js-hipay-bancomat-state--success" style="display:none">
                <h2>{l s='Payment confirmed' mod='hipaypayments'}</h2>
                <p>{l s='Thank you! Your order is now being processed.' mod='hipaypayments'}</p>
            </div>
            <div class="js-hipay-bancomat-state js-hipay-bancomat-state--failed" style="display:none">
                <h2>{l s='Payment failed' mod='hipaypayments'}</h2>
                <p>{l s='Your payment could not be processed. Please try again or contact our customer support.' mod='hipaypayments'}</p>
            </div>
            <div class="js-hipay-bancomat-state js-hipay-bancomat-state--timeout" style="display:none">
                <h2>{l s='Payment pending' mod='hipaypayments'}</h2>
                <p>{l s='Your order takes more time than expected to be completed. Please contact our customer support if your payment was accepted.' mod='hipaypayments'}</p>
            </div>
        </div>
    {else}
        <h1>{l s='Please wait, you will be redirected shortly' mod='hipaypayments'}</h1>
        <img id="js-hipay-loader" src="{$smarty.const.BASE_URL|escape:'htmlall':'UTF-8'}/modules/hipaypayments/views/img/icons/loader.svg" alt="Loading..." />
        <div id="js-hipay-timeout-message" class="alert alert-danger" style="display: none">
            {l s='Your order takes more time than expected to be completed. Please contact our customer support if your payment was accepted.' mod='hipaypayments'}
        </div>
    {/if}
{/block}
