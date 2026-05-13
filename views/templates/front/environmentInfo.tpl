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

<p class="alert alert-warning">
    {$environmentText|escape:'html':'UTF-8'}
    {if isset($testingCardsUrl)}
        <a href="{$testingCardsUrl|escape:'html':'UTF-8'}" target="_blank" style="text-decoration:underline;">
            {l s='Access to testing cards' mod='hipaypayments'} >>
        </a>
    {/if}
</p>
