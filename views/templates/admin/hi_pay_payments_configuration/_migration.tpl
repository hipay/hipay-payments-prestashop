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
    <div class="alert alert-info">
        <p>{l s='We noticed that you have the HiPay Enterprise module installed and enabled.' mod='hipayments'}</p>
        <p>
            {l s='You can choose to either migrate configuration data from this module or keep the default configuration.' mod='hipayments'}<br>
            {l s='In the latter case, you will need to configure HiPay Payments and disable the previous module manually.' mod='hipayments'}
        </p>
        <p>{l s='The migration process will copy credentials, payment methods settings and payment preferences. It will also disable HiPay Enterprise.' mod='hipayments'}</p>
    </div>
    <hr>
    <div id="js-hipay-payments-migration-choice-panel">
        <p>{l s='Do you want to migrate configuration data from the previous module?' mod='hipayments'}</p>
        <button type="button" href="#" id="js-hipay-payments-accept-migration" class="btn btn-primary">
            <i class="icon icon-check"></i> &nbsp;
            {l s='Yes, migrate data' mod='hipayments'}
        </button>
        <button type="button" href="#" id="js-hipay-payments-decline-migration" class="btn btn-danger">
            <i class="icon icon-times"></i> &nbsp;
            {l s='No, go to module\'s configuration' mod='hipayments'}
        </button>
    </div>

    <p></p>
    <div id="js-migration-progress-card" class="card mt-3" style="display:none;">
        <div class="card-body">
            <div class="progress mb-3">
                <div id="js-migration-global-progress"
                     class="progress-bar progress-bar-striped progress-bar-animated bg-info" role="progressbar"
                     style="width: 0%"></div>
            </div>

            <ul class="list-group list-group-flush" id="hipay-migration-steps-list">
                <li class="list-group-item d-flex justify-content-between align-items-center" data-step="credentials">
                    <span>
                        <i class="material-icons step-icon text-muted">hourglass_empty</i>
                        <span class="step-label">{l s='Credentials import' mod='hipayments'}</span>
                    </span>
                    <span class="badge badge-secondary step-status">{l s='Waiting' mod='hipayments'}</span>
                </li>

                <li class="list-group-item d-flex justify-content-between align-items-center" data-step="pm_update">
                    <span>
                        <i class="material-icons step-icon text-muted">hourglass_empty</i>
                        <span class="step-label">{l s='Payment methods update' mod='hipayments'}</span>
                    </span>
                    <span class="badge badge-secondary step-status">{l s='Waiting' mod='hipayments'}</span>
                </li>

                <li class="list-group-item d-flex justify-content-between align-items-center" data-step="card">
                    <span>
                        <i class="material-icons step-icon text-muted">hourglass_empty</i>
                        <span class="step-label">{l s='Card payment settings update' mod='hipayments'}</span>
                    </span>
                    <span class="badge badge-secondary step-status">{l s='Waiting' mod='hipayments'}</span>
                </li>

                <li class="list-group-item d-flex justify-content-between align-items-center" data-step="apm">
                    <span>
                        <i class="material-icons step-icon text-muted">hourglass_empty</i>
                        <span class="step-label">{l s='Advanced payment options update' mod='hipayments'}</span>
                    </span>
                    <span class="badge badge-secondary step-status">{l s='Waiting' mod='hipayments'}</span>
                </li>

                <li class="list-group-item d-flex justify-content-between align-items-center" data-step="db">
                    <span>
                        <i class="material-icons step-icon text-muted">hourglass_empty</i>
                        <span class="step-label">{l s='Database update' mod='hipayments'}</span>
                    </span>
                    <span class="badge badge-secondary step-status">{l s='Waiting' mod='hipayments'}</span>
                </li>
            </ul>

            <div id="js-migration-final-success" class="alert alert-success mt-3" style="display:none;">
                <p>{l s='Migration done successfully. This page will reload in a few seconds.' mod='hipayments'}</p>
            </div>

            <div id="js-migration-final-error" class="alert alert-danger mt-3" style="display:none;">
                <p>{l s='An error occurred while migrating data. You will need to configure the remaining data manually.' mod='hipayments'}</p>
                <br>
                <button type="button" href="#" id="js-hipay-payments-reload-page" class="btn btn-primary mt-3">
                    {l s='Go to module\'s configuration' mod='hipayments'}
                </button>
            </div>
        </div>
    </div>
</div>
