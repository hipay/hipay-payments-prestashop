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

{if isset($data['operationSuccess'])}
    {if true === $data['operationSuccess']}
        <div class="alert alert-success">
    {else}
        <div class="alert alert-danger">
    {/if}
        <p>{$data['operationMessage']|escape:'htmlall':'UTF-8'}</p>
    </div>
{/if}
<div class="info-block mb-2">
    <div class="col-sm text-center">
        <p class="text-muted mb-0">
            <strong>{l s='HiPay Order ID' mod='hipaypayments'}</strong>
        </p>
        <strong>{$data.hipayOrderId|escape:'htmlall':'UTF-8'}</strong>
    </div>

    <div class="col-sm text-center">
        <p class="text-muted mb-0">
            <strong>{l s='Transaction' mod='hipaypayments'}</strong>
        </p>
        <strong>{$data.reference|escape:'htmlall':'UTF-8'}</strong>
    </div>

    <div class="col-sm text-center">
        <p class="text-muted mb-0">
            <strong>{l s='Status' mod='hipaypayments'}</strong>
        </p>
        <strong>{$data.status|escape:'htmlall':'UTF-8'}</strong>
    </div>

    <div class="col-sm text-center">
        <p class="text-muted mb-0">
            <strong>{l s='Total' mod='hipaypayments'}</strong>
        </p>
        <span class="badge rounded badge-dark font-size-100">{$data.total|escape:'htmlall':'UTF-8'}</span>
    </div>

    <div class="col-sm text-center">
        <p class="text-muted mb-0">
            <strong>{l s='Payment product' mod='hipaypayments'}</strong>
        </p>
        <strong>{$data.paymentProduct|escape:'htmlall':'UTF-8'}</strong>
    </div>

    <div class="col-sm text-center">
        <p class="text-muted mb-0">
            <strong>{l s='3D-S Guaranteed' mod='hipaypayments'}</strong>
        </p>
        <span class="badge badge-status {if 1 === $data['3DSGuarantee']}badge-success{elseif -1 === $data['3DSGuarantee']}badge-secondary{else}badge-danger{/if} rounded">
            <i class="material-icons">{if 1 === $data['3DSGuarantee']}check{elseif -1 === $data['3DSGuarantee']}remove{else}close{/if}</i>
        </span>
    </div>
</div>
<div class="row">
    <div class="col-6">
        <div class="card">
            <div class="card-header">Capture</div>
            <div class="card-body">
                <div class="row mb-1">
                    <div class="col-6 text-right">
                        {l s='Captured amount' mod='hipaypayments'}
                    </div>
                    <div class="col-6">
                        <span class="price">{$data.capturedAmountDisplay|escape:'htmlall':'UTF-8'}</span>
                    </div>
                </div>
                <div class="row mb-1">
                    <div class="col-6 text-right">
                        {l s='Remaining amount to capture' mod='hipaypayments'}
                    </div>
                    <div class="col-6">
                        <span class="price">{$data.capturableAmountDisplay|escape:'htmlall':'UTF-8'}</span>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="button"
                        class="btn btn-primary mr-2"
                        id="js-hipay-full-capture"
                        data-amount-capturable="{$data.capturableAmount|floatval}"
                        data-modal-type="full-capture"
                        {if !$data.isCapturable}disabled="disabled"{/if}
                >
                    {l s='Full capture' mod='hipaypayments'}
                </button>
                <button type="button"
                        class="btn btn-primary"
                        data-toggle="modal"
                        data-target="#js-hipay-modal-operations"
                        data-modal-type="partial-capture"
                        data-amount-captured="{$data.capturedAmount|floatval}"
                        data-amount-capturable="{$data.capturableAmount|floatval}"
                        {if !$data.isCapturable}disabled="disabled"{/if}
                >
                    {l s='Partial capture' mod='hipaypayments'}
                </button>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="card">
            <div class="card-header">{l s='Refund' mod='hipaypayments'}</div>
            <div class="card-body">
                <div class="row mb-1">
                    <div class="col-6 text-right">
                        {l s='Refunded amount' mod='hipaypayments'}
                    </div>
                    <div class="col-6">
                        <span class="price">{$data.refundedAmount|escape:'htmlall':'UTF-8'}</span>
                    </div>
                </div>
                <div class="row mb-1">
                    <div class="col-6 text-right">
                        {l s='Remaining amount to refund' mod='hipaypayments'}
                    </div>
                    <div class="col-6">
                        <span class="price">{$data.refundableAmount|escape:'htmlall':'UTF-8'}</span>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="button"
                        class="btn btn-primary mr-2"
                        data-toggle="modal"
                        data-target="#js-hipay-modal-operations"
                        data-modal-type="refund"
                        data-amount-refunded="{$data.refundedAmount|floatval}"
                        data-amount-refundable="{$data.refundableAmount|floatval}"
                        {if !$data.isRefundable}disabled="disabled"{/if}
                >
                    {l s='Refund' mod='hipaypayments'}
                </button>
            </div>
        </div>
    </div>
</div>
