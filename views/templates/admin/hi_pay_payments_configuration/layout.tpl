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

<div class="row">
    <div class="col-lg-10 col-lg-offset-1">
        <div id="{$classPrefix|escape:'html':'UTF-8'}-configuration">
            {include file="./_header.tpl"}
            {if isset($displayMigrationContent)}
                {include file="./_migration.tpl"}
            {else}
                {include file="./_envBar.tpl"}
                <div class="form-wrapper">
                    <ul class="nav nav-tabs">
                        {foreach $data.tabs as $id => $tab}
                            <li {if $tab.active == true}class="active"{/if}>
                                <a href="#{$id|escape:'html':'UTF-8'}" data-toggle="tab">
                                    {if isset($tab.icon)}<i class="icon {$tab.icon|escape:'html':'UTF-8'}"></i>{/if}
                                    {$tab.title|escape:'html':'UTF-8'}
                                </a>
                            </li>
                        {/foreach}
                    </ul>
                    <div class="tab-content panel">
                        {foreach $data.tabs as $id => $tab}
                            <div id="{$id|escape:'html':'UTF-8'}" class="tab-pane {if $tab.active == true}active{/if}">
                                {include file=$tab.filename|escape:'html':'UTF-8'}
                            </div>
                        {/foreach}
                    </div>
                </div>
            {/if}
        </div>
    </div>
</div>
