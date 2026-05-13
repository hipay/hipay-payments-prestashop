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

<div class="panel">
    <form class="form-horizontal js-{$classPrefix|escape:'html':'UTF-8'}-card-payment-form"
          action="#"
          name="{$classPrefix|escape:'html':'UTF-8'}_cardPaymentSettings_form"
          id="{$classPrefix|escape:'html':'UTF-8'}-card-payment-form"
          method="post"
          enctype="multipart/form-data">
        <div class="panel-heading">{l s='Main parameters' mod='hipaypayments'}</div>
        <div class="panel-body">
            <div class="row">
                <div class="col-xs-12">
                    <!-- Display mode -->
                    <div class="form-group js-hp-display-mode-radio-block">
                        <label class="control-label col-lg-3">
                            <span>{l s='Payment page display' mod='hipaypayments'}</span>
                        </label>
                        <div class="col-lg-9 js-hp-display-mode-switch">
                            <div class="radio">
                                <label>
                                    <input type="radio"
                                           data-value="{$data.extra.const.DISPLAY_MODE_HOSTED_FIELDS|escape:'htmlall':'UTF-8'}"
                                           name="hpCardPaymentSettings[displayMode]"
                                           id="hp-mode-hosted-fields"
                                           value="{$data.extra.const.DISPLAY_MODE_HOSTED_FIELDS|escape:'htmlall':'UTF-8'}"
                                           {if $data.extra.const.DISPLAY_MODE_HOSTED_FIELDS === $data.cardPaymentSettings.displayMode}checked="checked"{/if}
                                    >
                                    {l s='Hosted Fields' mod='hipaypayments'}
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input type="radio"
                                           data-value="{$data.extra.const.DISPLAY_MODE_HOSTED_PAGE|escape:'htmlall':'UTF-8'}"
                                           name="hpCardPaymentSettings[displayMode]"
                                           id="hp-mode-hosted-page"
                                           value="{$data.extra.const.DISPLAY_MODE_HOSTED_PAGE|escape:'htmlall':'UTF-8'}"
                                           {if $data.extra.const.DISPLAY_MODE_HOSTED_PAGE === $data.cardPaymentSettings.displayMode}checked="checked"{/if}
                                    >
                                    {l s='Hosted Page' mod='hipaypayments'}
                                </label>
                            </div>
                        </div>
                    </div>
                    <!-- /Display Mode -->
                    <!-- Specific Hosted Fields -->
                    <div id="js-{$classPrefix|escape:'html':'UTF-8'}-mode-hosted-fields-block">
                        <!-- Font family -->
                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                {l s='Font family' mod='hipaypayments'}
                            </label>
                            <div class="col-lg-9">
                                <input type="text"
                                       class="fixed-width-lg"
                                       name="hpCardPaymentSettings[UISettings][fontFamily]"
                                       value="{$data.cardPaymentSettings.UISettings.fontFamily|escape:'htmlall':'UTF-8'}"/>
                            </div>
                        </div>
                        <!-- /Font family -->
                        <!-- Font size -->
                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                {l s='Font size' mod='hipaypayments'}
                            </label>
                            <div class="col-lg-2">
                                <div class="input-group">
                                    <input type="text"
                                           name="hpCardPaymentSettings[UISettings][fontSize]"
                                           value="{$data.cardPaymentSettings.UISettings.fontSize|intval}"/>
                                    <span class="input-group-addon">px</span>
                                </div>
                            </div>
                        </div>
                        <!-- /Font size -->
                        <!-- Font weight -->
                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                {l s='Font weight' mod='hipaypayments'}
                            </label>
                            <div class="col-lg-9">
                                <input type="text"
                                       class="fixed-width-lg"
                                       name="hpCardPaymentSettings[UISettings][fontWeight]"
                                       value="{$data.cardPaymentSettings.UISettings.fontWeight|intval}"/>
                            </div>
                        </div>
                        <!-- /Font weight -->
                        <!-- Color -->
                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                {l s='Color' mod='hipaypayments'}
                            </label>
                            <div class="col-lg-3">
                                <div class="input-group">
                                    <input type="color"
                                           data-hex="true"
                                           class="color mColorPickerInput"
                                           name="hpCardPaymentSettings[UISettings][color]"
                                           value="{$data.cardPaymentSettings.UISettings.color|escape:'htmlall':'UTF-8'}">
                                </div>
                            </div>
                        </div>
                        <!-- /Color -->
                        <!-- Placeholder Color -->
                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                {l s='Placeholder color' mod='hipaypayments'}
                            </label>
                            <div class="col-lg-3">
                                <div class="input-group">
                                    <input type="color"
                                           data-hex="true"
                                           class="color mColorPickerInput"
                                           name="hpCardPaymentSettings[UISettings][placeholderColor]"
                                           value="{$data.cardPaymentSettings.UISettings.placeholderColor|escape:'htmlall':'UTF-8'}">
                                </div>
                            </div>
                        </div>
                        <!-- /Placeholder Color -->
                        <!-- Caret Color -->
                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                {l s='Caret color' mod='hipaypayments'}
                            </label>
                            <div class="col-lg-3">
                                <div class="input-group">
                                    <input type="color"
                                           data-hex="true"
                                           class="color mColorPickerInput"
                                           name="hpCardPaymentSettings[UISettings][caretColor]"
                                           value="{$data.cardPaymentSettings.UISettings.caretColor|escape:'htmlall':'UTF-8'}">
                                </div>
                            </div>
                        </div>
                        <!-- /Caret Color -->
                        <!-- Icon Color -->
                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                {l s='Icon color' mod='hipaypayments'}
                            </label>
                            <div class="col-lg-3">
                                <div class="input-group">
                                    <input type="color"
                                           data-hex="true"
                                           class="color mColorPickerInput"
                                           name="hpCardPaymentSettings[UISettings][iconColor]"
                                           value="{$data.cardPaymentSettings.UISettings.iconColor|escape:'htmlall':'UTF-8'}">
                                </div>
                            </div>
                        </div>
                        <!-- /Icon Color -->
                        <!-- One Click -->
                        <div class="form-group js-hipay-one-click-block">
                            <label class="control-label col-lg-3 ">
                                {l s='Enable 1-click payment' mod='hipaypayments'}
                            </label>
                            <div class="col-lg-9 js-hipay-one-click-switch">
                            <span class="switch prestashop-switch fixed-width-sm">
                              <input type="radio"
                                     value="1"
                                     name="hpCardPaymentSettings[oneClickEnabled]"
                                     id="hpCardPaymentSettings_oneClickEnabled_on"
                                     {if $data.cardPaymentSettings.oneClickEnabled === true}checked="checked"{/if}>
                              <label for="hpCardPaymentSettings_oneClickEnabled_on">{l s='Yes' mod='hipaypayments'}</label>
                              <input type="radio"
                                     value="0"
                                     name="hpCardPaymentSettings[oneClickEnabled]"
                                     id="hpCardPaymentSettings_oneClickEnabled_off"
                                     {if $data.cardPaymentSettings.oneClickEnabled != true}checked="checked"{/if}>
                              <label for="hpCardPaymentSettings_oneClickEnabled_off">{l s='No' mod='hipaypayments'}</label>
                              <a class="slide-button btn"></a>
                            </span>
                            </div>
                        </div>
                        <!-- /One Click -->
                        <div class="js-hipay-one-click-enabled-block" style="display: none">
                            <!-- One Click Highlight Color -->
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    {l s='1-Click highlight color' mod='hipaypayments'}
                                </label>
                                <div class="col-lg-3">
                                    <div class="input-group">
                                        <input type="color"
                                               data-hex="true"
                                               class="color mColorPickerInput"
                                               name="hpCardPaymentSettings[UISettings][oneClickHighlightColor]"
                                               value="{$data.cardPaymentSettings.UISettings.oneClickHighlightColor|escape:'htmlall':'UTF-8'}">
                                    </div>
                                </div>
                            </div>
                            <!-- /One Click Highlight Color -->
                        </div>
                    </div>
                    <!-- /Specific Hosted Fields -->
                    <!-- Specific Hosted Page -->
                    <div id="js-{$classPrefix|escape:'html':'UTF-8'}-mode-hosted-page-block">
                        <!-- Hosted Page Type -->
                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                <span>{l s='Hosted Page type' mod='hipaypayments'}</span>
                            </label>
                            <div class="col-lg-9">
                                <div class="radio">
                                    <label>
                                        <input type="radio"
                                               data-value="{$data.extra.const.HOSTED_PAGE_TYPE_REDIRECT|escape:'htmlall':'UTF-8'}"
                                               name="hpCardPaymentSettings[hostedPageType]"
                                               id="hp-hosted-page-redirect"
                                               value="{$data.extra.const.HOSTED_PAGE_TYPE_REDIRECT|escape:'htmlall':'UTF-8'}"
                                               {if $data.extra.const.HOSTED_PAGE_TYPE_REDIRECT === $data.cardPaymentSettings.hostedPageType}checked="checked"{/if}
                                        >
                                        {l s='Redirect' mod='hipaypayments'}
                                    </label>
                                </div>
                                <div class="radio">
                                    <label>
                                        <input type="radio"
                                               data-value="{$data.extra.const.HOSTED_PAGE_TYPE_IFRAME|escape:'htmlall':'UTF-8'}"
                                               name="hpCardPaymentSettings[hostedPageType]"
                                               id="hp-hosted-page-iframe"
                                               value="{$data.extra.const.HOSTED_PAGE_TYPE_IFRAME|escape:'htmlall':'UTF-8'}"
                                               {if $data.extra.const.HOSTED_PAGE_TYPE_IFRAME === $data.cardPaymentSettings.hostedPageType}checked="checked"{/if}
                                        >
                                        {l s='Iframe' mod='hipaypayments'}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <!-- /Hosted Page Type -->
                        <!-- Display cancel button -->
                        <div class="form-group">
                            <label class="control-label col-lg-3 ">
                                {l s='Display cancel button' mod='hipaypayments'}
                            </label>
                            <div class="col-lg-9">
                                <span class="switch prestashop-switch fixed-width-sm">
                                    <input type="radio"
                                           value="1"
                                           name="hpCardPaymentSettings[cancelButtonDisplayed]"
                                           id="hpCardPaymentSettings_cancelButtonDisplayed_on"
                                           {if $data.cardPaymentSettings.cancelButtonDisplayed === true}checked="checked"{/if}>
                                    <label for="hpCardPaymentSettings_cancelButtonDisplayed_on">{l s='Yes' mod='hipaypayments'}</label>
                                    <input type="radio"
                                           value="0"
                                           name="hpCardPaymentSettings[cancelButtonDisplayed]"
                                           id="hpCardPaymentSettings_cancelButtonDisplayed_off"
                                           {if $data.cardPaymentSettings.cancelButtonDisplayed != true}checked="checked"{/if}>
                                    <label for="hpCardPaymentSettings_cancelButtonDisplayed_off">{l s='No' mod='hipaypayments'}</label>
                                    <a class="slide-button btn"></a>
                                </span>
                            </div>
                        </div>
                        <!-- /Display cancel button -->
                    </div>
                    <!-- /Specific Hosted Page -->
                    <!-- 3DS -->
                    <div class="form-group">
                        <label class="control-label col-lg-3" for="hpCardPaymentSettings_threeDSMode">
                            {l s='3DS mode' mod='hipaypayments'}
                        </label>
                        <div class="col-lg-9">
                            <select name="hpCardPaymentSettings[threeDSMode]" class="fixed-width-xxl" id="hpCardPaymentSettings_threeDSMode">
                                {foreach $data.extra.const.THREE_DS_MODES as $k => $mode}
                                    <option value="{$k|escape:'html':'UTF-8'}" {if $k === $data.cardPaymentSettings.threeDSMode}selected="selected"{/if}>
                                        {$mode|escape:'html':'UTF-8'}
                                    </option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <!-- /3DS -->
                </div>
            </div>
        </div>
        <input type="hidden" name="action" value="saveCardPaymentSettingsForm"/>
        <div class="panel-footer">
            <button type="submit" class="btn btn-default pull-right" name="submitSaveCardPaymentSettingsForm">
                <i class="process-icon-save"></i> {l s='Save' mod='hipaypayments'}
            </button>
        </div>
    </form>
</div>

<div class="panel">
    <form class="form-horizontal js-{$classPrefix|escape:'html':'UTF-8'}-cards-form"
          action="#"
          name="{$classPrefix|escape:'html':'UTF-8'}_cards_form"
          id="{$classPrefix|escape:'html':'UTF-8'}-cards-form"
          method="post"
          enctype="multipart/form-data">
        <div class="panel-heading">{l s='Cards settings' mod='hipaypayments'}</div>
        <div class="panel-body">
            <div class="row">
                <div class="alert alert-info">
                    <p>
                        {l s='Find below the card payment methods you are eligible for with your current contract.' mod='hipaypayments'}
                        <br>
                        {l s='You can enable or disable  them and control their display based on the criteria below, especially according to the order\'s currencies or countries.' mod='hipaypayments'}
                        <br>
                    </p>
                    <p>
                        {l s='Please note that available countries and currencies are those retrieved from' mod='hipaypayments'}
                        <a href="{$data.extra.links.paymentPreferences|escape:'htmlall':'UTF-8'}" target="_blank">
                            {l s='your configuration here' mod='hipaypayments'}
                            <i class="icon icon-external-link"></i>
                        </a>.
                    </p>
                </div>
                <div class="col-xs-12 {$classPrefix|escape:'html':'UTF-8'}-cards-list">
                    {foreach $data.cardPaymentSettings.paymentMethods as $k => $card}
                        <div class="panel">
                            <div class="row">
                                <span class="name {if true === $card.enabled}enabled{/if}">
                                    {if true === $card.enabled}<i class="icon icon-check"></i>{else}<i class="icon icon-times"></i>{/if}
                                    {$card.name|escape:'html':'UTF-8'}
                                </span>
                                <a data-toggle="collapse"
                                   href="#js-hipay-card-{$card.code|escape:'html':'UTF-8'}"
                                   role="button"
                                   aria-expanded="false"
                                   class="pull-right"
                                   type="button"
                                   aria-controls="js-hipay-card-{$card.code|escape:'html':'UTF-8'}">
                                    {l s='Expand / Collapse' mod='hipaypayments'}
                                </a>
                            </div>
                            <div class="row collapse" id="js-hipay-card-{$card.code|escape:'html':'UTF-8'}">
                                <hr>
                                <input type="hidden" name="hpCardPaymentSettings[paymentMethods][{$k|intval}][code]" value="{$data.cardPaymentSettings.paymentMethods[$k|intval]['code']|escape:'html':'UTF-8'}"/>
                                <input type="hidden" name="hpCardPaymentSettings[paymentMethods][{$k|intval}][name]" value="{$data.cardPaymentSettings.paymentMethods[$k|intval]['name']|escape:'html':'UTF-8'}"/>
                                <input type="hidden" name="hpCardPaymentSettings[paymentMethods][{$k|intval}][currencies]" value="[]"/>
                                <input type="hidden" name="hpCardPaymentSettings[paymentMethods][{$k|intval}][countries]" value="[]"/>
                                <input type="hidden" name="hpCardPaymentSettings[paymentMethods][{$k|intval}][canRefund]" value="{$data.cardPaymentSettings.paymentMethods[$k|intval]['canRefund']|escape:'html':'UTF-8'}"/>
                                <!-- Enabled -->
                                <div class="form-group">
                                    <label class="control-label col-lg-3 ">
                                        {l s='Enabled' mod='hipaypayments'}
                                    </label>
                                    <div class="col-lg-9">
                                        <span class="switch prestashop-switch fixed-width-sm">
                                            <input type="radio"
                                                   value="1"
                                                   name="hpCardPaymentSettings[paymentMethods][{$k|intval}][enabled]"
                                                   id="hpCardPaymentSettings_cpm_{$k|intval}_enabled_on"
                                                   {if $data.cardPaymentSettings.paymentMethods[$k|intval]['enabled'] === true}checked="checked"{/if}>
                                            <label for="hpCardPaymentSettings_cpm_{$k|intval}_enabled_on">{l s='Yes' mod='hipaypayments'}</label>
                                            <input type="radio"
                                                   value="0"
                                                   name="hpCardPaymentSettings[paymentMethods][{$k|intval}][enabled]"
                                                   id="hpCardPaymentSettings_cpm_{$k|intval}_enabled_off"
                                                   {if $data.cardPaymentSettings.paymentMethods[$k|intval]['enabled'] != true}checked="checked"{/if}>
                                            <label for="hpCardPaymentSettings_cpm_{$k|intval}_enabled_off">{l s='No' mod='hipaypayments'}</label>
                                            <a class="slide-button btn"></a>
                                        </span>
                                    </div>
                                </div>
                                <!-- /Enabled -->
                                <!-- Min Amount -->
                                <div class="form-group">
                                    <label class="control-label col-lg-3">
                                        {l s='Order minimal amount' mod='hipaypayments'}
                                    </label>
                                    <div class="col-lg-9">
                                        <div class="input-group">
                                            <span class="input-group-addon">{$data.extra.currencies.defaultIso|escape:'html':'UTF-8'}</span>
                                            <input type="text"
                                                   class="fixed-width-md"
                                                   name="hpCardPaymentSettings[paymentMethods][{$k|intval}][minAmount]"
                                                   onchange="hipayFormatAmountComplete(this)"
                                                   data-decimals="{$data.extra.currencies.defaultIsoDecimals|intval}"
                                                   value="{$data.cardPaymentSettings.paymentMethods[$k|intval]['minAmount']|floatval}"
                                            />
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
                                            <input type="text"
                                                   class="fixed-width-md"
                                                   name="hpCardPaymentSettings[paymentMethods][{$k|intval}][maxAmount]"
                                                   onchange="hipayFormatAmountComplete(this)"
                                                   data-decimals="{$data.extra.currencies.defaultIsoDecimals|intval}"
                                                   value="{$data.cardPaymentSettings.paymentMethods[$k|intval]['maxAmount']|floatval}"
                                            />
                                        </div>
                                    </div>
                                </div>
                                <!-- /Max Amount -->
                                <!-- Currencies & Countries specific -->
                                <div class="form-group js-hipay-cpm-specifics-{$k|intval}-radio-block">
                                    <label class="control-label col-lg-3 ">
                                        {l s='Manage countries & currencies specifics' mod='hipaypayments'}
                                    </label>
                                    <div class="col-lg-9 js-hipay-cpm-specifics-switch" data-cpm-key="{$k|intval}">
                                        <span class="switch prestashop-switch fixed-width-sm">
                                            <input type="radio"
                                                   value="1"
                                                   name="hpCardPaymentSettings[paymentMethods][{$k|intval}][currenciesCountriesManaged]"
                                                   id="hpCardPaymentSettings_cpm_{$k|intval}_currenciesCountriesManaged_on"
                                                   {if $data.cardPaymentSettings.paymentMethods[$k|intval]['currenciesCountriesManaged'] === true}checked="checked"{/if}>
                                            <label for="hpCardPaymentSettings_cpm_{$k|intval}_currenciesCountriesManaged_on">{l s='Yes' mod='hipaypayments'}</label>
                                            <input type="radio"
                                                   value="0"
                                                   name="hpCardPaymentSettings[paymentMethods][{$k|intval}][currenciesCountriesManaged]"
                                                   id="hpCardPaymentSettings_cpm_{$k|intval}_currenciesCountriesManaged_off"
                                                   {if $data.cardPaymentSettings.paymentMethods[$k|intval]['currenciesCountriesManaged'] != true}checked="checked"{/if}>
                                            <label for="hpCardPaymentSettings_cpm_{$k|intval}_currenciesCountriesManaged_off">{l s='No' mod='hipaypayments'}</label>
                                            <a class="slide-button btn"></a>
                                        </span>
                                    </div>
                                </div>
                                <!-- /Currencies & Countries specific -->
                                <!-- Specific block -->
                                <div class="js-hipay-cpm-{$k|intval}-specifics-block">
                                    <!-- Currencies -->
                                    <div class="form-group">
                                        <label class="control-label col-lg-3">{l s='Currencies' mod='hipaypayments'}</label>
                                        <div class="col-lg-9">
                                            <select id="hipay-multiselect-currencies-{$data.cardPaymentSettings.paymentMethods[$k|intval]['code']|escape:'html':'UTF-8'}"
                                                    name="hpCardPaymentSettings[paymentMethods][{$k|intval}][currencies][]"
                                                    multiple="multiple"
                                                    class="js-hipay-multiselect-currencies">
                                                {foreach $data.extra.currencies.list as $currency}
                                                    <option value="{$currency.iso_code|escape:'html':'UTF-8'}"
                                                            {if $currency.iso_code is in $data.cardPaymentSettings.paymentMethods[$k|intval]['currencies']}selected{/if}>
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
                                    </div>
                                    <!-- /Currencies -->
                                    <!-- Countries -->
                                    <div class="form-group">
                                        <label class="control-label col-lg-3">{l s='Countries' mod='hipaypayments'}</label>
                                        <div class="col-lg-9">
                                            <select id="hipay-multiselect-countries-{$data.cardPaymentSettings.paymentMethods[$k|intval]['code']|escape:'html':'UTF-8'}"
                                                    name="hpCardPaymentSettings[paymentMethods][{$k|intval}][countries][]"
                                                    multiple="multiple"
                                                    class="js-hipay-multiselect-countries">
                                                {foreach $data.extra.countries as $country}
                                                    <option value="{$country.iso_code|escape:'html':'UTF-8'}"
                                                            {if $country.iso_code is in $data.cardPaymentSettings.paymentMethods[$k|intval]['countries']}selected{/if}>
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
                                    </div>
                                    <!-- /Countries -->
                                </div>
                                <!-- /Specific block -->
                            </div>
                        </div>
                    {foreachelse}
                        <div class="alert alert-warning">
                            <p>{l s='There\'re no card payment methods. Please check your public credentials and the payment methods you are eligible for in your HiPay Back-Office.' mod='hipaypayments'}</p>
                        </div>
                    {/foreach}
                    {if $data.extra.unavailableCards && $data.cardPaymentSettings.paymentMethods}
                        <div class="alert alert-info">
                            <p>
                                {l s='HiPay also provides the following card payment options. Please reach out to your sales representative to activate them.' mod='hipaypayments'}
                            </p>
                        </div>
                        <div class="row {$classPrefix|escape:'html':'UTF-8'}-unavailable-cards-block">
                            {foreach $data.extra.unavailableCards as $card}
                                <div class="col-xs-4">
                                    <div class="panel text-center">
                                        {$card|escape:'html':'UTF-8'}
                                    </div>
                                </div>
                            {/foreach}
                        </div>
                    {/if}
                </div>
            </div>
        </div>
        <input type="hidden" name="action" value="saveCardsForm"/>
        <div class="panel-footer">
            <button type="submit" class="btn btn-default pull-right" name="submitSaveCardsForm">
                <i class="process-icon-save"></i> {l s='Save' mod='hipaypayments'}
            </button>
        </div>
    </form>
</div>
{literal}
<script type="text/javascript">
  $.fn.mColorPicker.defaults.imageFolder = baseDir + 'img/admin/';

  $('.js-hipay-multiselect-currencies').multiselect({
    includeSelectAllOption: true,
    selectAllText: '{/literal}{l s='Select / Unselect all' mod='hipayments'}{literal}',
    nonSelectedText: '{/literal}{l s='All currencies' mod='hipayments'}{literal}',
    allSelectedText: '{/literal}{l s='All currencies' mod='hipayments'}{literal}',
    nSelectedText: '{/literal}{l s='currencies selected' mod='hipayments'}{literal}',
  });
  $('.js-hipay-multiselect-countries').multiselect({
    includeSelectAllOption: true,
    selectAllText: '{/literal}{l s='Select / Unselect all' mod='hipayments'}{literal}',
    nonSelectedText: '{/literal}{l s='All countries' mod='hipayments'}{literal}',
    allSelectedText: '{/literal}{l s='All countries' mod='hipayments'}{literal}',
    nSelectedText: '{/literal}{l s='countries selected' mod='hipayments'}{literal}',
  });
</script>
{/literal}