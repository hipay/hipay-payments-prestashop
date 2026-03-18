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
