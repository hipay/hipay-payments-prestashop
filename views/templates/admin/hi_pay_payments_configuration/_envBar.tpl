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

<div class="{$classPrefix|escape:'html':'UTF-8'}-environment-bar panel {if true === $data.accountSettings.useDemoMode || $data.extra.const.ENV_TEST === $data.accountSettings.environment}env-test{/if}">
    <span>
        <i class="icon icon-warning"></i>
        {l s='You are using the' mod='hipaypayments'}
        {if true === $data.accountSettings.useDemoMode}
            {l s='demo mode.' mod='hipaypayments'}
        {elseif $data.extra.const.ENV_TEST === $data.accountSettings.environment}
            {l s='test environment.' mod='hipaypayments'}
        {else}
            {l s='production environment.' mod='hipaypayments'}
        {/if}
        {if true === $data.accountSettings.useDemoMode || $data.extra.const.ENV_TEST === $data.accountSettings.environment}
            {l s='Payments are simulated and do not result in any actual bank deposits.' mod='hipaypayments'}
        {else}
            {l s='Payments are not simulated and result in actual bank deposits.' mod='hipaypayments'}
        {/if}
    </span>
</div>
