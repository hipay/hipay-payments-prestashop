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

<form id="js-hipay-payments-form-{$hiPayHFData.formID}" class="hipay-payments-form" action="{$hiPayHFData.formAction}" method="post" name="hipay_payments_hosted_fields_form" style="position: relative">
    <div class="js-hipay-payments-hosted-fields-overlay-{$hiPayHFData.formID} hipay-payments-hosted-fields-overlay" style="display: none">
        <img src="{$smarty.const.BASE_URL|escape:'htmlall':'UTF-8'}/modules/hipaypayments/views/img/icons/loader.svg" alt="Loading..." />
    </div>
    <div id="js-hipay-payments-{$hiPayHFData.formID}-error-message" class="alert alert-danger" style="display: none"></div>
    <div id="js-hipay-payments-hosted-fields-form-{$hiPayHFData.formID}"></div>
    <input name="ioBB" id="ioBB-{$hiPayHFData.formID}" type="hidden" value="" />
</form>
