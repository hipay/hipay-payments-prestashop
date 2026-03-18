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

<div class="card">
    <div class="card-header">
        <h3 class="card-header-title">{l s='Products summary' mod='hipaypayments'}</h3>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
            <tr>
                <th>{l s='Product name' mod='hipaypayments'}</th>
                <th>{l s='Quantity' mod='hipaypayments'}</th>
                <th>{l s='Unit price tax incl.' mod='hipaypayments'}</th>
                <th>{l s='Total price tax incl.' mod='hipaypayments'}</th>
            </tr>
            </thead>
            <tbody>
            {foreach $data.orderDetails.productDetails as $productDetail}
                <tr>
                    <td>{$productDetail.productName|escape:'htmlall':'UTF-8'}</td>
                    <td>{$productDetail.productQuantity|intval}</td>
                    <td>
                        {$productDetail.unitPriceDisplay|escape:'htmlall':'UTF-8'}
                        {if false !== $productDetail.wasDisplay}
                            <br>
                            <small style="font-style: italic">
                                {l s='was' mod='hipaypayments'}
                                {$productDetail.originalPriceDisplay|escape:'htmlall':'UTF-8'}
                                {if false !== $productDetail.reductionDisplay}
                                    {$productDetail.reductionDisplay|escape:'htmlall':'UTF-8'}
                                {/if}
                            </small>
                        {/if}
                    </td>
                    <td>{$productDetail.totalPriceDisplay|escape:'htmlall':'UTF-8'}</td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </div>
</div>
{if $data.orderDetails.cartRules}
    <div class="card">
        <div class="card-header">
            <h3 class="card-header-title">{l s='Cart rules' mod='hipaypayments'}</h3>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                <tr>
                    <th>{l s='Order cart rule' mod='hipaypayments'}</th>
                    <th>{l s='Value tax incl.' mod='hipaypayments'}</th>
                </tr>
                </thead>
                <tbody>
                {foreach $data.orderDetails.cartRules as $cartRule}
                    <tr>
                        <td>{$cartRule.title|escape:'htmlall':'UTF-8'}</td>
                        <td>{$cartRule.value|escape:'htmlall':'UTF-8'}</td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>
{/if}
