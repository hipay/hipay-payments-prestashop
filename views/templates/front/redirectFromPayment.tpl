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
      const hipayCustomerToken = "{$hipayCustomerToken|escape:'javascript':'UTF-8'}";
      const hipayOrderId = "{$hipayOrderId|escape:'javascript':'UTF-8'}";
      const hipayTransactionReference = "{$hipayOrderId|escape:'javascript':'UTF-8'}";
      const idCart = "{$idCart|escape:'javascript':'UTF-8'}";
    </script>
{/block}

{block name='page_content_container'}
    <h1>{l s='Please wait, you will be redirected shortly' mod='hipaypayments'}</h1>

    <img id="js-hipay-loader" src="{$smarty.const.BASE_URL|escape:'htmlall':'UTF-8'}/modules/hipaypayments/views/img/icons/loader.svg" alt="Loading..." />
    <div id="js-hipay-timeout-message" class="alert alert-danger" style="display: none">
        {l s='Your order takes more time than expected to be completed. Please contact our customer support if your payment was accepted.' mod='hipaypayments'}
    </div>
{/block}
