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

<div class="panel js-panel-orderable" draggable="true"
    data-position="{$data.otherPMSettings.paymentMethods[$k|intval]['position']|intval}">
    <div class="row">
        <div class="position">
            <svg version="1.0" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                viewBox="0 0 143.000000 173.000000" preserveAspectRatio="xMidYMid meet">
                <g transform="translate(0.000000,173.000000) scale(0.100000,-0.100000)" fill="#000000" stroke="none">
                    <path d="M405 1615 c-5 -2 -22 -6 -37 -9 -37 -8 -98 -63 -114 -103 -24 -56
-18 -123 15 -175 51 -81 139 -107 228 -68 136 58 151 243 26 324 -36 22 -96
38 -118 31z" />
                    <path d="M943 1610 c-85 -20 -149 -112 -141 -201 10 -100 84 -164 188 -164 70
1 120 30 156 91 22 38 26 54 22 104 -3 48 -10 67 -35 97 -51 64 -120 90 -190
73z" />
                    <path d="M338 1030 c-20 -11 -50 -36 -65 -57 -25 -32 -28 -45 -28 -108 0 -63
3 -76 28 -108 113 -149 352 -61 335 123 -13 131 -156 210 -270 150z" />
                    <path d="M905 1033 c-48 -25 -73 -53 -91 -99 -70 -183 152 -335 300 -205 72
64 72 208 0 272 -56 49 -150 64 -209 32z" />
                    <path d="M357 476 c-48 -18 -74 -41 -98 -88 -62 -122 25 -269 159 -269 57 -1
88 10 129 45 67 56 80 169 29 247 -42 63 -143 93 -219 65z" />
                    <path d="M925 478 c-76 -28 -125 -97 -125 -174 0 -55 37 -123 86 -157 33 -23
51 -28 98 -28 67 0 105 19 149 74 26 31 32 49 35 100 3 55 0 67 -27 109 -16
26 -44 54 -61 63 -40 21 -117 28 -155 13z" />
                </g>
            </svg>
            <span class="js-hipay-position">{$data.otherPMSettings.paymentMethods[$k|intval]['position']|intval}</span>
            <i class="icon icon-chevron-up"></i>
            <i class="icon icon-chevron-down"></i>
        </div>
        <span class="name {if true === $paymentMethod.enabled}enabled{/if}">
            {if true === $paymentMethod.enabled}<i class="icon icon-check"></i>
            {else}<i class="icon icon-times"></i>
            {/if}
            {$paymentMethod.name|escape:'html':'UTF-8'}
        </span>
        <a data-toggle="collapse" href="#js-hipay-advanced-pm-{$paymentMethod.code|escape:'html':'UTF-8'}" role="button"
            aria-expanded="false" class="pull-right" type="button"
            aria-controls="js-hipay-advanced-pm-{$paymentMethod.code|escape:'html':'UTF-8'}">
            {l s='Expand / Collapse' mod='hipaypayments'}
        </a>
    </div>
    <div class="row collapse" id="js-hipay-advanced-pm-{$paymentMethod.code|escape:'html':'UTF-8'}">
        <hr>
        <input type="hidden" name="hpAdvancedPaymentSettings[paymentMethods][{$k|intval}][position]" value="{$data.otherPMSettings.paymentMethods[$k|intval]['position']|intval}" />
        <input type="hidden" name="hpAdvancedPaymentSettings[paymentMethods][{$k|intval}][displayMode]" value="{$data.otherPMSettings.paymentMethods[$k|intval]['displayMode']|escape:'html':'UTF-8'}" />
        <input type="hidden" name="hpAdvancedPaymentSettings[paymentMethods][{$k|intval}][code]" value="{$data.otherPMSettings.paymentMethods[$k|intval]['code']|escape:'html':'UTF-8'}" />
        <input type="hidden" name="hpAdvancedPaymentSettings[paymentMethods][{$k|intval}][name]" value="{$data.otherPMSettings.paymentMethods[$k|intval]['name']|escape:'html':'UTF-8'}" />
        <input type="hidden" name="hpAdvancedPaymentSettings[paymentMethods][{$k|intval}][minAmountForced]" value="{$data.otherPMSettings.paymentMethods[$k|intval]['minAmountForced']|intval}" />
        <input type="hidden" name="hpAdvancedPaymentSettings[paymentMethods][{$k|intval}][maxAmountForced]" value="{$data.otherPMSettings.paymentMethods[$k|intval]['maxAmountForced']|intval}" />
        <input type="hidden" name="hpAdvancedPaymentSettings[paymentMethods][{$k|intval}][countriesForced]" value="{$data.otherPMSettings.paymentMethods[$k|intval]['countriesForced']|intval}" />
        <input type="hidden" name="hpAdvancedPaymentSettings[paymentMethods][{$k|intval}][currenciesForced]" value="{$data.otherPMSettings.paymentMethods[$k|intval]['currenciesForced']|intval}" />
        <input type="hidden" name="hpAdvancedPaymentSettings[paymentMethods][{$k|intval}][canRefund]" value="{$data.otherPMSettings.paymentMethods[$k|intval]['canRefund']|intval}" />
        <input type="hidden" name="hpAdvancedPaymentSettings[paymentMethods][{$k|intval}][currencies]" value="[]" />
        <input type="hidden" name="hpAdvancedPaymentSettings[paymentMethods][{$k|intval}][countries]" value="[]" />
        <!-- Enabled -->
        <div class="form-group">
            <label class="control-label col-lg-3 ">
                {l s='Enabled' mod='hipaypayments'}
            </label>
            <div class="col-lg-9">
                <span class="switch prestashop-switch fixed-width-sm">
                    <input type="radio" value="1" name="hpAdvancedPaymentSettings[paymentMethods][{$k|intval}][enabled]"
                        id="hpAdvancedPaymentSettings_pm_{$k|intval}_enabled_on"
                        {if $data.otherPMSettings.paymentMethods[$k|intval]['enabled'] === true}checked="checked" {/if}>
                    <label
                        for="hpAdvancedPaymentSettings_pm_{$k|intval}_enabled_on">{l s='Yes' mod='hipaypayments'}</label>
                    <input type="radio" value="0" name="hpAdvancedPaymentSettings[paymentMethods][{$k|intval}][enabled]"
                        id="hpAdvancedPaymentSettings_pm_{$k|intval}_enabled_off"
                        {if $data.otherPMSettings.paymentMethods[$k|intval]['enabled'] != true}checked="checked" {/if}>
                    <label
                        for="hpAdvancedPaymentSettings_pm_{$k|intval}_enabled_off">{l s='No' mod='hipaypayments'}</label>
                    <a class="slide-button btn"></a>
                </span>
            </div>
        </div>
        <!-- /Enabled -->
        <!-- Specifics -->
        {include file="./`$code`.tpl"}
        <!-- /Specifics -->
        <!-- Min Amount -->
        <div class="form-group">
            <label class="control-label col-lg-3">
                {l s='Order minimal amount' mod='hipaypayments'}
            </label>
            <div class="col-lg-9">
                <div class="input-group">
                    <span class="input-group-addon">{$data.extra.currencies.defaultIso|escape:'html':'UTF-8'}</span>
                    <input type="text" class="fixed-width-md"
                        {if $data.otherPMSettings.paymentMethods[$k|intval]['minAmountForced']}readonly{/if}
                        name="hpAdvancedPaymentSettings[paymentMethods][{$k|intval}][minAmount]"
                        onchange="hipayFormatAmountComplete(this)"
                        data-decimals="{$data.extra.currencies.defaultIsoDecimals|intval}"
                        value="{$data.otherPMSettings.paymentMethods[$k|intval]['minAmount']|floatval}" />
                </div>
            </div>
        </div>
        <!-- /Min Amount -->
        <!-- Max Amount -->
        <div class="form-group">
            <label class="control-label col-lg-3">
                {l s='Order maximal amount' mod='hipaypayments'}
            </label>
            <div class="col-lg-9">
                <div class="input-group">
                    <span class="input-group-addon">{$data.extra.currencies.defaultIso|escape:'html':'UTF-8'}</span>
                    <input type="text" class="fixed-width-md"
                        {if $data.otherPMSettings.paymentMethods[$k|intval]['maxAmountForced']}readonly{/if}
                        name="hpAdvancedPaymentSettings[paymentMethods][{$k|intval}][maxAmount]"
                        onchange="hipayFormatAmountComplete(this)"
                        data-decimals="{$data.extra.currencies.defaultIsoDecimals|intval}"
                        value="{$data.otherPMSettings.paymentMethods[$k|intval]['maxAmount']|floatval}" />
                </div>
            </div>
        </div>
        <!-- /Max Amount -->
        <input type="hidden" name="hpAdvancedPaymentSettings[paymentMethods][{$k|intval}][currenciesCountriesManaged]"
            value="1" />
        <!-- Currencies -->
        <div class="form-group">
            <label class="control-label col-lg-3">{l s='Currencies' mod='hipaypayments'}</label>
            {if $data.otherPMSettings.paymentMethods[$k|intval]['currenciesForced']}
                <div class="col-lg-9" style="padding-top: 10px;">
                    {foreach $data.extra.currencies.list as $currency}
                        {if $currency.iso_code is in $data.otherPMSettings.paymentMethods[$k|intval]['currencies'] || !$data.otherPMSettings.paymentMethods[$k|intval]['currencies']}
                            <p>{$currency.name|escape:'html':'UTF-8'} - {$currency.iso_code|escape:'html':'UTF-8'}</p>
                        {/if}
                    {/foreach}
                    {if isset($data.otherPMSettings.paymentMethods[$k|intval]['missingCurrencies'])}
                        {foreach $data.otherPMSettings.paymentMethods[$k|intval]['missingCurrencies'] as $missingCurrency}
                            <p>{$missingCurrency|escape:'html':'UTF-8'} (<i class="icon icon-warning"></i>
                                {l s='currency not installed or disabled' mod='hipaypayments'})</p>
                        {/foreach}
                    {/if}
                    {foreach $data.otherPMSettings.paymentMethods[$k|intval]['currencies'] as $currencyIsoCode}
                        <input type="hidden" name="hpAdvancedPaymentSettings[paymentMethods][{$k|intval}][currencies][]"
                            value="{$currencyIsoCode|escape:'html':'UTF-8'}" />
                    {/foreach}
                </div>
            {else}
                <div class="col-lg-9">
                    <select
                        id="hipay-multiselect-currencies-{$data.otherPMSettings.paymentMethods[$k|intval]['code']|escape:'html':'UTF-8'}"
                        name="hpAdvancedPaymentSettings[paymentMethods][{$k|intval}][currencies][]" multiple="multiple"
                        class="js-hipay-multiselect-currencies">
                        {foreach $data.extra.currencies.list as $currency}
                            <option value="{$currency.iso_code|escape:'html':'UTF-8'}"
                                {if $currency.iso_code is in $data.otherPMSettings.paymentMethods[$k|intval]['currencies']}selected{/if}>
                                {$currency.name|escape:'html':'UTF-8'} - {$currency.iso_code|escape:'html':'UTF-8'}
                            </option>
                        {/foreach}
                    </select>
                </div>
                <div class="col-lg-9 col-lg-offset-3">
                    <div class="help-block">
                        <i class="icon icon-info-sign"></i>
                        {l s='If no currency is selected, all currencies will be associated to this payment method.' mod='hipaypayments'}
                        <span></span>
                    </div>
                </div>
            {/if}
        </div>
        <!-- /Currencies -->
        <!-- Countries -->
        <div class="form-group">
            <label class="control-label col-lg-3">{l s='Countries' mod='hipaypayments'}</label>
            {if $data.otherPMSettings.paymentMethods[$k|intval]['currenciesForced']}
                <div class="col-lg-9" style="padding-top: 10px;">
                    {foreach $data.extra.countries as $country}
                        {if $country.iso_code is in $data.otherPMSettings.paymentMethods[$k|intval]['countries'] || !$data.otherPMSettings.paymentMethods[$k|intval]['countries']}
                            <p>{$country.name|escape:'html':'UTF-8'}</p>
                        {/if}
                    {/foreach}
                    {if isset($data.otherPMSettings.paymentMethods[$k|intval]['missingCountries'])}
                        {foreach $data.otherPMSettings.paymentMethods[$k|intval]['missingCountries'] as $missingCountry}
                            <p>{$missingCountry|escape:'html':'UTF-8'} (<i class="icon icon-warning"></i>
                                {l s='country not installed or disabled' mod='hipaypayments'})</p>
                        {/foreach}
                    {/if}
                    {foreach $data.otherPMSettings.paymentMethods[$k|intval]['countries'] as $countryIsoCode}
                        <input type="hidden" name="hpAdvancedPaymentSettings[paymentMethods][{$k|intval}][countries][]"
                            value="{$countryIsoCode|escape:'html':'UTF-8'}" />
                    {/foreach}
                </div>
            {else}
                <div class="col-lg-9">
                    <select
                        id="hipay-multiselect-countries-{$data.otherPMSettings.paymentMethods[$k|intval]['code']|escape:'html':'UTF-8'}"
                        name="hpAdvancedPaymentSettings[paymentMethods][{$k|intval}][countries][]" multiple="multiple"
                        class="js-hipay-multiselect-countries">
                        {foreach $data.extra.countries as $country}
                            <option value="{$country.iso_code|escape:'html':'UTF-8'}"
                                {if $country.iso_code is in $data.otherPMSettings.paymentMethods[$k|intval]['countries']}selected{/if}>
                                {$country.name|escape:'html':'UTF-8'}
                            </option>
                        {/foreach}
                    </select>
                </div>
                <div class="col-lg-9 col-lg-offset-3">
                    <div class="help-block">
                        <i class="icon icon-info-sign"></i>
                        {l s='If no country is selected, all countries will be associated to this payment method.' mod='hipaypayments'}
                        <span></span>
                    </div>
                </div>
            {/if}
        </div>
        <!-- /Countries -->
    </div>
</div>