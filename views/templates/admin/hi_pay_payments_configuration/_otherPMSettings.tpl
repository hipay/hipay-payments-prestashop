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

{if $data.mainSettings.hostedPageEnabled}
    <div class="panel js-hipay-hosted-page-display-block">
        <form class="form-horizontal js-hipay-hosted-page-display-form"
              action="#"
              method="post">
            <div class="panel-heading">{l s='Display at checkout' mod='hipaypayments'}</div>
            <div class="panel-body">
                <!-- Hosted Page button label -->
                <div class="form-group">
                    <label class="control-label col-lg-3">
                        {l s='Hosted Page button label' mod='hipaypayments'}
                    </label>
                    <div class="col-lg-9">
                        {foreach $data.extra.languages as $lang}
                            <div class="input-group hipay-lang-input" data-lang="{$lang.id_lang|intval}" {if !$lang@first}style="display:none"{/if}>
                                <span class="input-group-addon">{$lang.iso_code|escape:'html':'UTF-8'}</span>
                                <input type="text"
                                       name="hpMainSettings[hostedPageLabel][{$lang.id_lang|intval}]"
                                       value="{if isset($data.mainSettings.hostedPageLabel[$lang.id_lang])}{$data.mainSettings.hostedPageLabel[$lang.id_lang]|escape:'html':'UTF-8'}{/if}"
                                       placeholder="{l s='Secured Payments' mod='hipaypayments'}"
                                       class="form-control">
                            </div>
                        {/foreach}
                        {if count($data.extra.languages) > 1}
                            <div class="btn-group hipay-lang-switch" style="margin-top: 5px;">
                                {foreach $data.extra.languages as $lang}
                                    <button type="button" class="btn btn-default js-hipay-lang-btn {if $lang@first}active{/if}"
                                            data-lang="{$lang.id_lang|intval}">{$lang.iso_code|escape:'html':'UTF-8'}</button>
                                {/foreach}
                            </div>
                        {/if}
                    </div>
                </div>
                <!-- /Hosted Page button label -->
                <!-- Hosted Page button position -->
                <div class="form-group">
                    <label class="control-label col-lg-3">
                        {l s='Button position' mod='hipaypayments'}
                    </label>
                    <div class="col-lg-9">
                        <div class="radio">
                            <label>
                                <input type="radio" name="hpMainSettings[hostedPagePosition]" value="above"
                                       {if $data.mainSettings.hostedPagePosition != 'below'}checked="checked"{/if}>
                                {l s='Above the other payment methods' mod='hipaypayments'}
                            </label>
                        </div>
                        <div class="radio">
                            <label>
                                <input type="radio" name="hpMainSettings[hostedPagePosition]" value="below"
                                       {if $data.mainSettings.hostedPagePosition == 'below'}checked="checked"{/if}>
                                {l s='Below the other payment methods' mod='hipaypayments'}
                            </label>
                        </div>
                    </div>
                </div>
                <!-- /Hosted Page button position -->
            </div>
            <input type="hidden" name="action" value="saveMainSettingsForm"/>
            <div class="panel-footer">
                <button type="submit" class="btn btn-default pull-right" name="submitSaveHostedPageDisplayForm">
                    <i class="process-icon-save"></i> {l s='Save' mod='hipaypayments'}
                </button>
            </div>
        </form>
    </div>
{/if}

<div class="panel">
    <form class="form-horizontal js-{$classPrefix|escape:'html':'UTF-8'}-advanced-payment-form"
          action="#"
          name="{$classPrefix|escape:'html':'UTF-8'}_advancedPayment_form"
          id="{$classPrefix|escape:'html':'UTF-8'}-advanced-payment-form"
          method="post"
          enctype="multipart/form-data">
        <div class="panel-heading">{l s='Advanced payment methods settings' mod='hipaypayments'}</div>
        <div class="panel-body">
            <div class="row">
                <div class="alert alert-info">
                    <p>{l s='The alternative payment methods for which you are eligible under your current contract are listed below.' mod='hipaypayments'}</p>
                    <p>{l s='You can arrange the order of payment options displayed on your checkout page by using drag and drop between the blocks below.' mod='hipaypayments'}</p>
                </div>
                <div class="col-xs-12 {$classPrefix|escape:'html':'UTF-8'}-advanced-pm-list">
                    {assign var="hasHostedFieldsPM" value=false}
                    {foreach $data.otherPMSettings.paymentMethods as $k => $paymentMethod}
                        {if $paymentMethod.channel != 'hosted_page'}
                            {assign var="hasHostedFieldsPM" value=true}
                            {include file="./apm/default.tpl" code=$paymentMethod.code}
                        {/if}
                    {/foreach}
                    {if !$hasHostedFieldsPM}
                        <div class="alert alert-warning">
                            <p>{l s='There\'re no alternative payment methods. Please check your public credentials and the payment methods you are eligible for in your HiPay Back-Office.' mod='hipaypayments'}</p>
                        </div>
                    {/if}
                    <p></p>
                    {if $data.extra.unavailableAPM && $data.otherPMSettings.paymentMethods}
                        <div class="alert alert-info">
                            <p>
                                {l s='HiPay also provides the following advanced payment options. Please reach out to your sales representative to activate them.' mod='hipaypayments'}
                            </p>
                        </div>
                        <div class="row {$classPrefix|escape:'html':'UTF-8'}-unavailable-cards-block">
                            {foreach $data.extra.unavailableAPM as $pm}
                                <div class="col-xs-4">
                                    <div class="panel text-center">
                                        {$pm|escape:'html':'UTF-8'}
                                    </div>
                                </div>
                            {/foreach}
                        </div>
                    {/if}
                </div>
            </div>
            {if $data.mainSettings.hostedPageEnabled}
                <div class="row js-hipay-hosted-page-pm-block">
                    <hr>
                    <div class="alert alert-info">
                        <p><strong>{l s='Hosted Page' mod='hipaypayments'}</strong></p>
                        <p>{l s='Payment methods enabled here are handled by the HiPay Hosted Page instead of hosted fields, and are automatically removed from the list above.' mod='hipaypayments'}</p>
                    </div>
                    <div class="col-xs-12 {$classPrefix|escape:'html':'UTF-8'}-advanced-pm-list">
                        {assign var="hasHostedPagePM" value=false}
                        {foreach $data.otherPMSettings.paymentMethods as $k => $paymentMethod}
                            {if $paymentMethod.channel == 'hosted_page'}
                                {assign var="hasHostedPagePM" value=true}
                                {include file="./apm/default.tpl" code=$paymentMethod.code}
                            {/if}
                        {/foreach}
                        {if !$hasHostedPagePM}
                            <div class="alert alert-warning">
                                <p>{l s='No payment method is currently routed to the Hosted Page. Switch a method\'s channel below to add it here.' mod='hipaypayments'}</p>
                            </div>
                        {/if}
                    </div>
                </div>
            {/if}
        </div>
        <input type="hidden" name="action" value="saveAPMForm"/>
        <div class="panel-footer">
            <button type="submit" class="btn btn-default pull-right" name="submitSaveAPMForm">
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
