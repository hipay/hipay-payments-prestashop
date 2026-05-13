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

{if $data.moduleInfo.newVersionAvailable}
  <div class="alert alert-warning">
    <p>
      {l s='A new version %s of this module is available.' sprintf=[$data.moduleInfo.latestVersionAvailable|escape:'html':'UTF-8'] mod='hipaypayments'}
      {l s='More details on' mod='hipaypayments'}
      <a href="{$data.moduleInfo.releaseUrl|escape:'html':'UTF-8'}" target="_blank">
        {l s='this page' mod='hipaypayment'}
        <i class="icon icon-external-link"></i>
      </a>
    </p>
    <p>
      {l s='You can download the zip file by following this URL:' mod='hipaypayment'}
      <a href="{$data.moduleInfo.assetUrl|escape:'html':'UTF-8'}" target="_blank">
        {$data.moduleInfo.assetUrl|escape:'html':'UTF-8'}
      </a>
    </p>
  </div>
{/if}
<div class="{$classPrefix|escape:'html':'UTF-8'}-information">
  <i class="icon icon-info-circle"></i>
  {l s='HiPay Payments' mod='hipaypayments'}
  v{$data.extra.moduleVersion|escape:'htmlall':'UTF-8'}
  {if !$data.moduleInfo.newVersionAvailable}
    | {l s='No updates available' mod='hipaypayments'}
    <i class="icon icon-check"></i>
  {/if}
</div>
<div class="panel">
  <div class="row">
    <div class="{$classPrefix|escape:'html':'UTF-8'}-header flex col-xs-12">
      <div class="{$classPrefix|escape:'html':'UTF-8'}-logo">
        <img src="/modules/hipaypayments/views/img/logos/logo-hipay.png" alt="Logo"/>
      </div>
      <div class="{$classPrefix|escape:'html':'UTF-8'}-support flex">
        <div class="contact flex">
          <i class="icon icon-question-circle icon-big flex"></i>
          <div class="flex">
            <p><b>{l s='Do you have a question?' mod='hipaypayments'}</b></p>
            <p>{l s='Contact us using' mod='hipaypayments'}
              <a href="mailto:support.ent@hipay.com">
                {l s='this link' mod='hipaypayments'}
              </a>
            </p>
          </div>
          <div class="flex">
            <p>
              <a class="btn btn-primary js-hipay-payments-health-check-btn" data-toggle="modal" data-target="#js-hipay-payments-health-check-modal">
                {l s='Check module health' mod='hipaypayments'}
              </a>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
