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

{extends file='customer/page.tpl'}

{block name='page_title'}
    {l s='Stored credit/debit cards' mod='hipaypayments'}
{/block}

{block name='page_content'}
    <div class="table-wrapper">
        <table class="table table-striped d-none d-xl-table">
            <thead class="thead-default">
            <tr>
                <th>{l s='Card brand' mod='hipaypayments'}</th>
                <th>{l s='Card number' mod='hipaypayments'}</th>
                <th>{l s='Card holder' mod='hipaypayments'}</th>
                <th>{l s='Expiry date' mod='hipaypayments'}</th>
                <th>{l s='Actions' mod='hipaypayments'}</th>
            </tr>
            </thead>
            <tbody>
            {foreach $customerTokens as $customerToken}
                <tr>
                    <td>{$customerToken.brand|escape:'html':'UTF-8'}</td>
                    <td>{$customerToken.pan|escape:'html':'UTF-8'}</td>
                    <td>{$customerToken.card_holder|escape:'html':'UTF-8'}</td>
                    <td>{$customerToken.card_expiry_month|escape:'html':'UTF-8'}/{$customerToken.card_expiry_year|escape:'html':'UTF-8'}</td>
                    <td>
                        <a href="{$link->getModuleLink('hipaypayments', 'storedcards', ['deleteCard' => 1, 'token' => $token|escape:'html':'UTF-8', 'cardId' => $customerToken.id|intval])}">
                            <span class="material-icons">delete</span>
                            {l s='Delete' mod='hipaypayments'}
                        </a>
                    </td>
                </tr>
            {foreachelse}
                <tr>
                    <td colspan="5">
                        <div class="alert alert-info">
                            {l s='You do not have any stored cards yet.' mod='hipaypayments'}
                        </div>
                    </td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </div>
{/block}
