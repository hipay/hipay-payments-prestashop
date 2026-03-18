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

<form name="hipay_capture_form"
      method="post"
      class="form-horizontal"
>
    <div class="modal-header">
        <h5 class="modal-title">{l s='Capture funds' mod='hipaypayments'}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="{l s='Close' mod='hipaypayments'}">
            <span aria-hidden="true">×</span>
        </button>
    </div>
    <div class="modal-body">
        {include file="module:hipaypayments/views/templates/admin/new-theme/_partials/modalOperationsOrderDetails.tpl"}

        <div class="hipay-order-summary">
            <div class="info-block mb-2">
                <div class="col-sm text-center">
                    <p class="text-muted mb-0">
                        <strong>{l s='Shipping Total' mod='hipaypayments'}</strong>
                    </p>
                    <span class="badge rounded badge-dark font-size-100">{$data.orderDetails.shippingTotalDisplay|escape:'htmlall':'UTF-8'}</span>
                </div>
                {if false !== $data.orderDetails.cartRules}
                    <div class="col-sm text-center">
                        <p class="text-muted mb-0">
                            <strong>{l s='Cart rules Total' mod='hipaypayments'}</strong>
                        </p>
                        <span class="badge rounded badge-dark font-size-100">-{$data.orderDetails.cartRulesTotalDisplay|escape:'htmlall':'UTF-8'}</span>
                    </div>
                {/if}
                <div class="col-sm text-center">
                    <p class="text-muted mb-0">
                        <strong>{l s='Order Total' mod='hipaypayments'}</strong>
                    </p>
                    <span class="badge rounded badge-dark font-size-100">{$data.orderDetails.orderTotalDisplay|escape:'htmlall':'UTF-8'}</span>
                </div>
                <div class="col-sm text-center">
                    <p class="text-muted mb-0">
                        <strong>{l s='Amount already captured' mod='hipaypayments'}</strong>
                    </p>
                    <span class="badge rounded badge-success font-size-100">{$data.orderDetails.amountCaptured|escape:'htmlall':'UTF-8'}</span>
                </div>
            </div>
            <hr>
            <div class="row mb-1 form-group">
                <label class="form-control-label col-4 text-right"
                       for="amountToCapture">{l s='Amount to capture' mod='hipaypayments'}</label>
                <div class="input-group col-8">
                    <input
                            type="text"
                            class="form-control col-4"
                            data-decimals="{$data.orderDetails.currencyDecimals|intval}"
                            id="amountToCapture"
                            name="data[amount]"
                            onchange="hipayFormatAmountComplete(this)"
                            value="{$data.orderDetails.amountCapturable|escape:'htmlall':'UTF-8'}"
                    />
                    <div class="input-group-append">
                        <span class="input-group-text" id="basic-addon1">{$data.orderDetails.currencyCode|escape:'htmlall':'UTF-8'}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" name="data[operationType]" value="partial-capture" />
    <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
            {l s='Cancel' mod='hipaypayments'}
        </button>
        <button type="submit" class="btn btn-primary">
            {l s='Capture' mod='hipaypayments'}
        </button>
    </div>
</form>
