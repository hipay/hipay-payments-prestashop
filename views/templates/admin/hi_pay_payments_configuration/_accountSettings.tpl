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
    <form class="form-horizontal js-{$classPrefix|escape:'html':'UTF-8'}-account-form"
          action="#"
          name="{$classPrefix|escape:'html':'UTF-8'}_accountSettings_form"
          id="{$classPrefix|escape:'html':'UTF-8'}-accountSettings-form"
          method="post"
          enctype="multipart/form-data">
        <div class="row">
            <div class="col-xs-12">
                {foreach $data.accountSettings.hashingAlgorithms as $key => $hashingAlgorithm}
                    <input type="hidden" name="hpAccountSettings[hashingAlgorithms][{$key|escape:'html':'UTF-8'}]" value="{$hashingAlgorithm|escape:'htmlall':'UTF-8'}" />
                {/foreach}
                {if $data.accountSettings.useDemoMode === true}
                    <!-- DEMO Mode -->
                    <div class="form-group js-{$classPrefix|escape:'html':'UTF-8'}-enable-demo-mode-block">
                        <label class="control-label col-lg-3 ">
                            {l s='Use demo mode' mod='hipaypayments'}
                        </label>
                        <div class="col-lg-9 js-{$classPrefix|escape:'html':'UTF-8'}-enable-demo-mode-switch">
                            <span class="switch prestashop-switch fixed-width-sm">
                              <input type="radio"
                                     value="1"
                                     name="hpAccountSettings[useDemoMode]"
                                     id="hpAccountSettings_useDemoMode_on"
                                     {if $data.accountSettings.useDemoMode === true}checked="checked"{/if}>
                              <label for="hpAccountSettings_useDemoMode_on">{l s='Yes' mod='hipaypayments'}</label>
                              <input type="radio"
                                     value="0"
                                     name="hpAccountSettings[useDemoMode]"
                                     id="hpAccountSettings_useDemoMode_off"
                                     {if $data.accountSettings.useDemoMode != true}checked="checked"{/if}>
                              <label for="hpAccountSettings_useDemoMode_off">{l s='No' mod='hipaypayments'}</label>
                              <a class="slide-button btn"></a>
                            </span>
                        </div>
                    </div>
                    <!-- /DEMO Mode -->
                    <div class="alert alert-info">
                        <p>
                            {l s='You are currently using the DEMO mode. Only cards payments are functional.' mod='hipaypayments'}
                            {l s='Please note that payments are simulated and do not result in any actual bank deposits.' mod='hipaypayments'}
                            {l s='You can find Test cards' mod='hipaypayments'}
                            <a href="{$data.extra.links.testCardsUrl|escape:'htmlall':'UTF-8'}" target="_blank">
                                {l s='on this page' mod='hipaypayments'}
                                <i class="icon icon-external-link"></i>
                            </a>.
                        </p>
                        <p>
                            <i class="icon icon-info-sign"></i>
                            {l s='After receiving and configuring your Test & Production credentials below, Demo mode will be removed.' mod='hipaypayments'}
                            {l s='You can restore it by resetting the module.' mod='hipaypayments'}
                        </p>
                    </div>
                {else}
                    <input type="radio"
                           value="0"
                           style="display: none"
                           name="hpAccountSettings[useDemoMode]"
                           checked="checked">
                {/if}
                <!-- Environment -->
                <input type="hidden"
                       name="hpAccountSettings[environment]"
                       value="{$data.accountSettings.environment|escape:'htmlall':'UTF-8'}"
                >

                <div class="form-group js-hp-environment-radio-block">
                    <label class="control-label col-lg-3">
                        <span>{l s='Environment' mod='hipaypayments'}</span>
                    </label>
                    <div class="col-lg-9 js-hp-environment-switch">
                        <div class="radio">
                            <label>
                                <input type="radio"
                                       data-value="{$data.extra.const.ENV_TEST|escape:'htmlall':'UTF-8'}"
                                       name="hpAccountSettings[environment]"
                                       id="hp-env-test"
                                       value="{$data.extra.const.ENV_TEST|escape:'htmlall':'UTF-8'}"
                                       {if $data.extra.const.ENV_TEST === $data.accountSettings.environment}checked="checked"{/if}
                                >
                                {l s='Test' mod='hipaypayments'}
                            </label>
                        </div>
                        <div class="radio">
                            <label>
                                <input type="radio"
                                       data-value="{$data.extra.const.ENV_PRODUCTION|escape:'htmlall':'UTF-8'}"
                                       name="hpAccountSettings[environment]"
                                       id="hp-env-prod"
                                       value="{$data.extra.const.ENV_PRODUCTION|escape:'htmlall':'UTF-8'}"
                                       {if $data.extra.const.ENV_PRODUCTION === $data.accountSettings.environment}checked="checked"{/if}
                                >
                                {l s='Production' mod='hipaypayments'}
                            </label>
                        </div>
                    </div>
                </div>
                <!-- /Environment -->
                <!-- TEST Credentials -->
                <div id="js-{$classPrefix|escape:'html':'UTF-8'}-env-test-block">
                    <div class="panel">
                        <div class="panel-heading">{l s='Test private identifiers' mod='hipaypayments'}</div>
                        <div class="panel-body">
                            <!-- TEST Username -->
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    {l s='Test username' mod='hipaypayments'}
                                </label>
                                <div class="col-lg-9">
                                    <input type="text" name="hpAccountSettings[testPrivateIdentifiers][username]"
                                           value="{$data.accountSettings.testPrivateIdentifiers.username|escape:'htmlall':'UTF-8'}"/>
                                </div>
                            </div>
                            <!-- /TEST Username -->
                            <!-- TEST Password -->
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    {l s='Test password' mod='hipaypayments'}
                                </label>
                                <div class="col-lg-9">
                                    <input type="password" name="hpAccountSettings[testPrivateIdentifiers][password]"
                                           value="{$data.accountSettings.testPrivateIdentifiers.password|escape:'htmlall':'UTF-8'}"/>
                                </div>
                            </div>
                            <!-- /TEST Password -->
                            <!-- TEST Secret -->
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    {l s='Test Passphrase' mod='hipaypayments'}
                                </label>
                                <div class="col-lg-9">
                                    <input type="password" name="hpAccountSettings[testPrivateIdentifiers][secret]"
                                           value="{$data.accountSettings.testPrivateIdentifiers.secret|escape:'htmlall':'UTF-8'}"/>
                                </div>
                            </div>
                            <!-- /TEST Secret -->
                            <hr>
                            <!-- APPLE PAY TEST Username -->
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    {l s='ApplePay Test username' mod='hipaypayments'}
                                </label>
                                <div class="col-lg-9">
                                    <input type="text" name="hpAccountSettings[applePayTestPrivateIdentifiers][username]"
                                           value="{$data.accountSettings.applePayTestPrivateIdentifiers.username|escape:'htmlall':'UTF-8'}"/>
                                </div>
                            </div>
                            <!-- /APPLE PAY TEST Username -->
                            <!-- APPLE PAY TEST Password -->
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    {l s='ApplePay Test password' mod='hipaypayments'}
                                </label>
                                <div class="col-lg-9">
                                    <input type="password" name="hpAccountSettings[applePayTestPrivateIdentifiers][password]"
                                           value="{$data.accountSettings.applePayTestPrivateIdentifiers.password|escape:'htmlall':'UTF-8'}"/>
                                </div>
                            </div>
                            <!-- /APPLE PAY TEST Password -->
                            <!-- APPLE PAY TEST Secret -->
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    {l s='ApplePay Test Passphrase' mod='hipaypayments'}
                                </label>
                                <div class="col-lg-9">
                                    <input type="password" name="hpAccountSettings[applePayTestPrivateIdentifiers][secret]"
                                           value="{$data.accountSettings.applePayTestPrivateIdentifiers.secret|escape:'htmlall':'UTF-8'}"/>
                                </div>
                            </div>
                            <!-- /APPLE PAY TEST Secret -->
                            <hr>
                            <!-- MOTO TEST Username -->
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    {l s='MOTO Test username' mod='hipaypayments'}
                                </label>
                                <div class="col-lg-9">
                                    <input type="text" name="hpAccountSettings[motoTestPrivateIdentifiers][username]"
                                           value="{$data.accountSettings.motoTestPrivateIdentifiers.username|escape:'htmlall':'UTF-8'}"/>
                                </div>
                            </div>
                            <!-- /MOTO TEST Username -->
                            <!-- MOTO TEST Password -->
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    {l s='MOTO Test password' mod='hipaypayments'}
                                </label>
                                <div class="col-lg-9">
                                    <input type="password" name="hpAccountSettings[motoTestPrivateIdentifiers][password]"
                                           value="{$data.accountSettings.motoTestPrivateIdentifiers.password|escape:'htmlall':'UTF-8'}"/>
                                </div>
                            </div>
                            <!-- /MOTO TEST Password -->
                            <!-- MOTO TEST Secret -->
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    {l s='MOTO Test Passphrase' mod='hipaypayments'}
                                </label>
                                <div class="col-lg-9">
                                    <input type="password" name="hpAccountSettings[motoTestPrivateIdentifiers][secret]"
                                           value="{$data.accountSettings.motoTestPrivateIdentifiers.secret|escape:'htmlall':'UTF-8'}"/>
                                </div>
                            </div>
                            <!-- /MOTO TEST Secret -->
                        </div>
                    </div>
                    <div class="panel">
                        <div class="panel-heading">{l s='Test public identifiers' mod='hipaypayments'}</div>
                        <div class="panel-body">
                            <!-- TEST Username -->
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    {l s='Test username' mod='hipaypayments'}
                                </label>
                                <div class="col-lg-9">
                                    <input type="text" name="hpAccountSettings[testPublicIdentifiers][username]"
                                           value="{$data.accountSettings.testPublicIdentifiers.username|escape:'htmlall':'UTF-8'}"/>
                                </div>
                            </div>
                            <!-- /TEST Username -->
                            <!-- TEST Password -->
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    {l s='Test password' mod='hipaypayments'}
                                </label>
                                <div class="col-lg-9">
                                    <input type="password" name="hpAccountSettings[testPublicIdentifiers][password]"
                                           value="{$data.accountSettings.testPublicIdentifiers.password|escape:'htmlall':'UTF-8'}"/>
                                </div>
                            </div>
                            <!-- /TEST Password -->
                            <hr>
                            <!-- APPLE PAY TEST Username -->
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    {l s='ApplePay Test username' mod='hipaypayments'}
                                </label>
                                <div class="col-lg-9">
                                    <input type="text" name="hpAccountSettings[applePayTestPublicIdentifiers][username]"
                                           value="{$data.accountSettings.applePayTestPublicIdentifiers.username|escape:'htmlall':'UTF-8'}"/>
                                </div>
                            </div>
                            <!-- /APPLE PAY TEST Username -->
                            <!-- APPLE PAY TEST Password -->
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    {l s='ApplePay Test password' mod='hipaypayments'}
                                </label>
                                <div class="col-lg-9">
                                    <input type="password" name="hpAccountSettings[applePayTestPublicIdentifiers][password]"
                                           value="{$data.accountSettings.applePayTestPublicIdentifiers.password|escape:'htmlall':'UTF-8'}"/>
                                </div>
                            </div>
                            <!-- /APPLE PAY TEST Password -->
                        </div>
                    </div>
                </div>
                <!-- /TEST Credentials -->
                <!-- PROD Credentials -->
                <div id="js-{$classPrefix|escape:'html':'UTF-8'}-env-prod-block">
                    <div class="panel">
                        <div class="panel-heading">{l s='Production private identifiers' mod='hipaypayments'}</div>
                        <div class="panel-body">
                            <!-- PROD Username -->
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    {l s='Production username' mod='hipaypayments'}
                                </label>
                                <div class="col-lg-9">
                                    <input type="text" name="hpAccountSettings[prodPrivateIdentifiers][username]"
                                           value="{$data.accountSettings.prodPrivateIdentifiers.username|escape:'htmlall':'UTF-8'}"/>
                                </div>
                            </div>
                            <!-- /PROD Username -->
                            <!-- PROD Password -->
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    {l s='Production password' mod='hipaypayments'}
                                </label>
                                <div class="col-lg-9">
                                    <input type="password" name="hpAccountSettings[prodPrivateIdentifiers][password]"
                                           value="{$data.accountSettings.prodPrivateIdentifiers.password|escape:'htmlall':'UTF-8'}"/>
                                </div>
                            </div>
                            <!-- /PROD Password -->
                            <!-- PROD Secret -->
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    {l s='Production secret' mod='hipaypayments'}
                                </label>
                                <div class="col-lg-9">
                                    <input type="text" name="hpAccountSettings[prodPrivateIdentifiers][secret]"
                                           value="{$data.accountSettings.prodPrivateIdentifiers.secret|escape:'htmlall':'UTF-8'}"/>
                                </div>
                            </div>
                            <!-- /PROD Secret -->
                            <hr>
                            <!-- APPLE PAY PROD Username -->
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    {l s='ApplePay Production username' mod='hipaypayments'}
                                </label>
                                <div class="col-lg-9">
                                    <input type="text" name="hpAccountSettings[applePayProdPrivateIdentifiers][username]"
                                           value="{$data.accountSettings.applePayProdPrivateIdentifiers.username|escape:'htmlall':'UTF-8'}"/>
                                </div>
                            </div>
                            <!-- /APPLE PAY PROD Username -->
                            <!-- APPLE PAY PROD Password -->
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    {l s='ApplePay Production password' mod='hipaypayments'}
                                </label>
                                <div class="col-lg-9">
                                    <input type="password" name="hpAccountSettings[applePayProdPrivateIdentifiers][password]"
                                           value="{$data.accountSettings.applePayProdPrivateIdentifiers.password|escape:'htmlall':'UTF-8'}"/>
                                </div>
                            </div>
                            <!-- /APPLE PAY PROD Password -->
                            <!-- APPLE PAY PROD Secret -->
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    {l s='ApplePay Production secret' mod='hipaypayments'}
                                </label>
                                <div class="col-lg-9">
                                    <input type="text" name="hpAccountSettings[applePayProdPrivateIdentifiers][secret]"
                                           value="{$data.accountSettings.applePayProdPrivateIdentifiers.secret|escape:'htmlall':'UTF-8'}"/>
                                </div>
                            </div>
                            <!-- /APPLE PAY PROD Secret -->
                            <hr>
                            <!-- MOTO PROD Username -->
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    {l s='MOTO Production username' mod='hipaypayments'}
                                </label>
                                <div class="col-lg-9">
                                    <input type="text" name="hpAccountSettings[motoProdPrivateIdentifiers][username]"
                                           value="{$data.accountSettings.motoProdPrivateIdentifiers.username|escape:'htmlall':'UTF-8'}"/>
                                </div>
                            </div>
                            <!-- /MOTO PROD Username -->
                            <!-- MOTO PROD Password -->
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    {l s='MOTO Production password' mod='hipaypayments'}
                                </label>
                                <div class="col-lg-9">
                                    <input type="password" name="hpAccountSettings[motoProdPrivateIdentifiers][password]"
                                           value="{$data.accountSettings.motoProdPrivateIdentifiers.password|escape:'htmlall':'UTF-8'}"/>
                                </div>
                            </div>
                            <!-- /MOTO PROD Password -->
                            <!-- MOTO PROD Secret -->
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    {l s='MOTO Production secret' mod='hipaypayments'}
                                </label>
                                <div class="col-lg-9">
                                    <input type="text" name="hpAccountSettings[motoProdPrivateIdentifiers][secret]"
                                           value="{$data.accountSettings.motoProdPrivateIdentifiers.secret|escape:'htmlall':'UTF-8'}"/>
                                </div>
                            </div>
                            <!-- /MOTO PROD Secret -->
                        </div>
                    </div>
                    <div class="panel">
                        <div class="panel-heading">{l s='Production public identifiers' mod='hipaypayments'}</div>
                        <div class="panel-body">
                            <!-- PROD Username -->
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    {l s='Production username' mod='hipaypayments'}
                                </label>
                                <div class="col-lg-9">
                                    <input type="text" name="hpAccountSettings[prodPublicIdentifiers][username]"
                                           value="{$data.accountSettings.prodPublicIdentifiers.username|escape:'htmlall':'UTF-8'}"/>
                                </div>
                            </div>
                            <!-- /PROD Username -->
                            <!-- PROD Password -->
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    {l s='Production password' mod='hipaypayments'}
                                </label>
                                <div class="col-lg-9">
                                    <input type="password" name="hpAccountSettings[prodPublicIdentifiers][password]"
                                           value="{$data.accountSettings.prodPublicIdentifiers.password|escape:'htmlall':'UTF-8'}"/>
                                </div>
                            </div>
                            <!-- /PROD Password -->
                            <hr>
                            <!-- APPLE PAY PROD Username -->
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    {l s='ApplePay Production username' mod='hipaypayments'}
                                </label>
                                <div class="col-lg-9">
                                    <input type="text" name="hpAccountSettings[applePayProdPublicIdentifiers][username]"
                                           value="{$data.accountSettings.applePayProdPublicIdentifiers.username|escape:'htmlall':'UTF-8'}"/>
                                </div>
                            </div>
                            <!-- /APPLE PAY PROD Username -->
                            <!-- APPLE PAY PROD Password -->
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    {l s='ApplePay Production password' mod='hipaypayments'}
                                </label>
                                <div class="col-lg-9">
                                    <input type="password" name="hpAccountSettings[applePayProdPublicIdentifiers][password]"
                                           value="{$data.accountSettings.applePayProdPublicIdentifiers.password|escape:'htmlall':'UTF-8'}"/>
                                </div>
                            </div>
                            <!-- /APPLE PAY PROD Password -->
                        </div>
                    </div>
                </div>
                <!-- /PROD Credentials -->
                <!-- Cron -->
                <div class="alert alert-info">
                    <p>
                        {l s='We strongly recommend installing a Cron job on your server to ensure regular updates of your transaction statuses.' mod='hipaypayments'}
                        {l s='We recommend running the cron job every 10 minutes.' mod='hipaypayments'}
                    </p>
                    <p>
                        {l s='Here\'s an example of a cron command for execution every 10 minutes:' mod='hipaypayments'}<br>
                    </p>
                    <p>
                        <code style="white-space: nowrap; overflow-x: auto; display: block; max-width: 100%; margin: 20px 0;" data-copy-text="{l s='Copy the line' mod='hipaypayments'}" data-copied-text="{l s='Copied!' mod='hipaypayments'}">
                            */10 * * * * curl -s "{$data.extra.urls.notifyCron|escape:'htmlall':'UTF-8'}" >/dev/null 2>&1
                        </code>
                        <button type="button" class="btn btn-primary mt-1" onclick='const code = this.previousElementSibling; navigator.clipboard.writeText(code.textContent.trim()).then(() => { this.innerHTML = "<i class=\"icon icon-copy\"></i>&nbsp;" + code.dataset.copiedText; setTimeout(() => this.innerHTML = "<i class=\"icon icon-copy\"></i>&nbsp;" + code.dataset.copyText, 2000); })'>
                            <i class="icon icon-copy"></i>&nbsp;
                            {l s='Copy the line' mod='hipaypayments'}
                        </button>
                    </p>
                </div>
                <!-- /Cron -->
            </div>
        </div>
        <input type="hidden" name="action" value="saveAccountForm"/>
        <div class="panel-footer">
            <button type="submit" class="btn btn-default pull-right" name="submitSaveAccountForm">
                <i class="process-icon-save"></i> {l s='Save' mod='hipaypayments'}
            </button>
            <button type="submit" class="btn btn-default pull-right" name="submitSaveCheckCredentials">
                <i class="process-icon-save"></i> {l s='Save & Check credentials' mod='hipaypayments'}
            </button>
        </div>
    </form>
</div>
