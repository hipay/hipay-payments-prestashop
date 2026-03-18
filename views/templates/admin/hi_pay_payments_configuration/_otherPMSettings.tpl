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
                    {foreach $data.otherPMSettings.paymentMethods as $k => $paymentMethod}
                        {include file="./apm/default.tpl" code=$paymentMethod.code}
                    {foreachelse}
                        <div class="alert alert-warning">
                            <p>{l s='There\'re no alternative payment methods. Please check your public credentials and the payment methods you are eligible for in your HiPay Back-Office.' mod='hipaypayments'}</p>
                        </div>
                    {/foreach}
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
