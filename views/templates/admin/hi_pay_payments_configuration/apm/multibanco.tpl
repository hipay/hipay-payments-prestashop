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

<!-- Expiration Limit -->
<div class="form-group">
    <label class="control-label col-lg-3" for="hpAdvancedPaymentSettings_threeDSMode">
        {l s='Order expiration' mod='hipaypayments'}
    </label>
    <div class="col-lg-9">
        <select name="hpAdvancedPaymentSettings[paymentMethods][{$k|intval}][expirationLimit]" class="fixed-width-xxl" id="hpAdvancedPaymentSettings_pm_{$k|intval}_expirationLimit">
            {foreach $data.extra.const.MULTIBANCO.EXPIRATION_LIMITS as $limit}
                <option value="{$limit|intval}" {if $limit === $data.otherPMSettings.paymentMethods[$k|intval]['expirationLimit']}selected="selected"{/if}>
                    {$limit|intval} {l s='days' mod='hipaypayments'}
                </option>
            {/foreach}
        </select>
    </div>
</div>
<!-- /Expiration Limit -->
