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

<form class="form-horizontal js-{$classPrefix|escape:'html':'UTF-8'}-main-settings-form"
      action="#"
      name="{$classPrefix|escape:'html':'UTF-8'}_mainSettings_form"
      id="{$classPrefix|escape:'html':'UTF-8'}-main-settings-form"
      method="post"
      enctype="multipart/form-data">
    <div class="panel">
        <div class="row">
            <div class="col-xs-12">
                <div class="panel">
                    <div class="panel-heading">{l s='Payment settings' mod='hipaypayments'}</div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-xs-12">
                                <!-- Capture type -->
                                <div class="form-group">
                                    <label class="control-label col-lg-3">
                                        <span>{l s='Capture type' mod='hipaypayments'}</span>
                                    </label>
                                    <div class="col-lg-9">
                                        <div class="radio">
                                            <label>
                                                <input type="radio"
                                                       data-value="{$data.extra.const.CAPTURE_AUTO|escape:'htmlall':'UTF-8'}"
                                                       name="hpMainSettings[captureMode]"
                                                       id="hp-capture-auto"
                                                       value="{$data.extra.const.CAPTURE_AUTO|escape:'htmlall':'UTF-8'}"
                                                       {if $data.extra.const.CAPTURE_AUTO === $data.mainSettings.captureMode}checked="checked"{/if}
                                                >
                                                {l s='Automatic' mod='hipaypayments'}
                                            </label>
                                        </div>
                                        <div class="radio">
                                            <label>
                                                <input type="radio"
                                                       data-value="{$data.extra.const.CAPTURE_MANUAL|escape:'htmlall':'UTF-8'}"
                                                       name="hpMainSettings[captureMode]"
                                                       id="hp-capture-manual"
                                                       value="{$data.extra.const.CAPTURE_MANUAL|escape:'htmlall':'UTF-8'}"
                                                       {if $data.extra.const.CAPTURE_MANUAL === $data.mainSettings.captureMode}checked="checked"{/if}
                                                >
                                                {l s='Manual' mod='hipaypayments'}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <!-- /Capture type -->
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-heading">{l s='Module settings' mod='hipaypayments'}</div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-xs-12">
                                <!-- Logs -->
                                <div class="form-group">
                                    <label class="control-label col-lg-3 ">
                                        {l s='Enable verbose logs' mod='hipaypayments'}
                                    </label>
                                    <div class="col-lg-9">
                                        <span class="switch prestashop-switch fixed-width-sm">
                                            <input type="radio"
                                                 value="1"
                                                 name="hpMainSettings[verboseLogsEnabled]"
                                                 id="hpMainSettings_verboseLogsEnabled_on"
                                                 {if $data.mainSettings.verboseLogsEnabled === true}checked="checked"{/if}>
                                            <label for="hpMainSettings_verboseLogsEnabled_on">{l s='Yes' mod='hipaypayments'}</label>
                                            <input type="radio"
                                                 value="0"
                                                 name="hpMainSettings[verboseLogsEnabled]"
                                                 id="hpMainSettings_verboseLogsEnabled_off"
                                                 {if $data.mainSettings.verboseLogsEnabled != true}checked="checked"{/if}>
                                            <label for="hpMainSettings_verboseLogsEnabled_off">{l s='No' mod='hipaypayments'}</label>
                                            <a class="slide-button btn"></a>
                                        </span>
                                    </div>
                                    <div class="col-lg-9 col-lg-offset-3">
                                        <div class="help-block">
                                            {l s='The minimum log level will be set to Debug.' mod='hipaypayments'}
                                            {l s='Logs will be accumulated for 30 days after activation.' mod='hipaypayments'}
                                            {l s='Older files can be accessed on your server, in the "logs" directory of this module.' mod='hipaypayments'}

                                            <br/>
                                            <span></span>
                                        </div>
                                        <a class="btn btn-primary" href="{$link->getAdminLink('AdminHiPayPaymentsLogs', true, [], ['action' => 'downloadLogFile'])|escape:'html':'UTF-8'}">
                                            <i class="icon icon-download"></i>&nbsp;
                                            {l s='Download latest file' mod='hipaypayments'}
                                        </a>
                                    </div>
                                </div>
                                <!-- /Logs -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" name="action" value="saveMainSettingsForm"/>
        <div class="panel-footer">
            <button type="submit" class="btn btn-default pull-right" name="submitSaveCardsForm">
                <i class="process-icon-save"></i> {l s='Save' mod='hipaypayments'}
            </button>
        </div>
    </div>
</form>
