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

<div class="panel hipay-payments-basic-info">
    <div class="panel-heading">
        <h3 class="panel-title">{l s='Basic informations' mod='hipaypayments'}</h3>
    </div>
    <div class="panel-body">
        <div>
            <div>
                <p><b>{l s='Architecture details' mod='hipaypayments'}</b></p>
                <p>
                    <span>{l s='PrestaShop version:' mod='hipaypayments'}</span>
                    {$data.basicInfo.psVersion|escape:'html':'UTF-8'}
                </p>
                <p>
                    <span>{l s='PHP version:' mod='hipaypayments'}</span>
                    {$data.basicInfo.phpVersion|escape:'html':'UTF-8'}
                </p>
            </div>
            <div>
                <p><b>{l s='Module versions' mod='hipaypayments'}</b></p>
                <p>
                    <span>{l s='Current version:' mod='hipaypayments'}</span>
                    {$data.basicInfo.currentModuleVersion|escape:'html':'UTF-8'}
                </p>
                <p>
                    <span>{l s='Latest version:' mod='hipaypayments'}</span>
                    {$data.basicInfo.latestModuleVersion|escape:'html':'UTF-8'}
                </p>
                <p>
                    <span>{l s='Latest update check:' mod='hipaypayments'}</span>
                    {$data.basicInfo.latestModuleUpdateCheck|escape:'html':'UTF-8'}
                </p>
            </div>
            <div>
                <p><b>{l s='Module informations' mod='hipaypayments'}</b></p>
                <p>
                    <span>{l s='Environment / Mode:' mod='hipaypayments'}</span>
                    {$data.basicInfo.environment|escape:'html':'UTF-8'}
                </p>
                <p>
                    <span>{l s='Hashing algorithms:' mod='hipaypayments'}</span>
                    <ul>
                        {foreach $data.basicInfo.moduleHashingAlgorithms as $algorithmDetails}
                            <li>{$algorithmDetails.title|escape:'html':'UTF-8'} : {$algorithmDetails.value|escape:'html':'UTF-8'}</li>
                        {/foreach}
                    </ul>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="panel hipay-payments-checks">
    <div class="panel-heading">
        <h3 class="panel-title">{l s='Checks' mod='hipaypayments'}</h3>
    </div>
    <div class="panel-body">
        {foreach $data.checks as $check}
            <p>
                <i class="icon {$check.icon|escape:'html':'UTF-8'}"></i>
                <b>{$check.title|escape:'html':'UTF-8'}:</b>
                {$check.value|escape:'html':'UTF-8'}
            </p>
        {/foreach}
    </div>
</div>
