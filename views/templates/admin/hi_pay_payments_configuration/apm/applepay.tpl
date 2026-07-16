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

<div class="form-group">
    <label class="control-label col-lg-3">
        {l s='ApplePay Merchant Identifier' mod='hipaypayments'}
    </label>
    <div class="col-lg-9">
        <input type="text"
               class="fixed-width-xxl"
               name="hpAdvancedPaymentSettings[paymentMethods][{$k|intval}][merchantIdentifier]"
               value="{$data.otherPMSettings.paymentMethods[$k|intval]['merchantIdentifier']|escape:'htmlall':'UTF-8'}"/>
    </div>
</div>

<div class="form-group">
    <label class="control-label col-lg-3">
        {l s='Enable Apple Pay multi-browser' mod='hipaypayments'}
    </label>
    <div class="col-lg-9">
        <span class="switch prestashop-switch fixed-width-sm">
            <input type="radio" value="1"
                   name="hpAdvancedPaymentSettings[paymentMethods][{$k|intval}][multiBrowserEnabled]"
                   id="hpAdvancedPaymentSettings_pm_{$k|intval}_multiBrowserEnabled_on"
                   class="js-hipay-applepay-multibrowser-toggle"
                   data-target="#js-hipay-applepay-displaymode-{$k|intval}"
                   {if $data.otherPMSettings.paymentMethods[$k|intval]['multiBrowserEnabled'] === true}checked="checked"{/if}>
            <label for="hpAdvancedPaymentSettings_pm_{$k|intval}_multiBrowserEnabled_on">{l s='Yes' mod='hipaypayments'}</label>
            <input type="radio" value="0"
                   name="hpAdvancedPaymentSettings[paymentMethods][{$k|intval}][multiBrowserEnabled]"
                   id="hpAdvancedPaymentSettings_pm_{$k|intval}_multiBrowserEnabled_off"
                   class="js-hipay-applepay-multibrowser-toggle"
                   data-target="#js-hipay-applepay-displaymode-{$k|intval}"
                   {if $data.otherPMSettings.paymentMethods[$k|intval]['multiBrowserEnabled'] !== true}checked="checked"{/if}>
            <label for="hpAdvancedPaymentSettings_pm_{$k|intval}_multiBrowserEnabled_off">{l s='No' mod='hipaypayments'}</label>
            <a class="slide-button btn"></a>
        </span>
    </div>
</div>

<div class="form-group" id="js-hipay-applepay-displaymode-{$k|intval}"
    {if $data.otherPMSettings.paymentMethods[$k|intval]['multiBrowserEnabled'] !== true}style="display:none;"{/if}>
    <label class="control-label col-lg-3">
        {l s='Display mode' mod='hipaypayments'}
    </label>
    <div class="col-lg-9">
        <select class="fixed-width-xxl"
                name="hpAdvancedPaymentSettings[paymentMethods][{$k|intval}][multiBrowserDisplayMode]">
            <option value="popup"
                {if $data.otherPMSettings.paymentMethods[$k|intval]['multiBrowserDisplayMode'] === 'popup'}selected="selected"{/if}>
                popup
            </option>
            <option value="modal"
                {if $data.otherPMSettings.paymentMethods[$k|intval]['multiBrowserDisplayMode'] === 'modal'}selected="selected"{/if}>
                modal
            </option>
        </select>
    </div>
</div>

<script>
(function () {
    document.querySelectorAll('.js-hipay-applepay-multibrowser-toggle').forEach(function (radio) {
        radio.addEventListener('change', function () {
            var target = document.querySelector(this.getAttribute('data-target'));
            if (!target) return;
            target.style.display = (this.value === '1') ? '' : 'none';
        });
    });
}());
</script>