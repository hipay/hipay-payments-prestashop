/*
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
 */

document.addEventListener('DOMContentLoaded', () => {
  const btnDecline = document.getElementById('js-hipay-payments-decline-migration');
  const btnAccept = document.getElementById('js-hipay-payments-accept-migration');
  const panelChoice = document.getElementById('js-hipay-payments-migration-choice-panel');
  const cardProgress = document.getElementById('js-migration-progress-card');
  const progressBar = document.getElementById('js-migration-global-progress');
  const alertSuccess = document.getElementById('js-migration-final-success');
  const migrationFinalError = document.getElementById('js-migration-final-error');
  const btnMigrationDoneError = document.getElementById('js-hipay-payments-reload-page');

  const toggleButtons = (disabledState) => {
    if (btnDecline) btnDecline.disabled = disabledState;
    if (btnAccept) btnAccept.disabled = disabledState;
  };

  if (btnDecline) {
    btnDecline.addEventListener('click', function() {
      toggleButtons(true);

      const formData = new FormData();
      formData.append('ajax', '1');
      formData.append('action', 'declineMigration');

      fetch(hpAjaxController, {
        method: 'POST',
        body: formData
      })
        .then(response => response.json())
        .then(data => {
          window.location.reload();
        })
        .catch(error => {
          console.error('Error:', error);
          toggleButtons(false);
        });
    });
  }

  if (btnAccept) {
    btnAccept.addEventListener('click', async function() {
      toggleButtons(true);

      panelChoice.disabled = true;
      cardProgress.style.display = 'block';

      const steps = [
        { id: 'credentials', action: 'migrateCredentials' },
        { id: 'pm_update',   action: 'updatePaymentMethods' },
        { id: 'card', action: 'migrateCardSettings' },
        { id: 'apm',  action: 'migrateAdvancedPaymentMethodsSettings' },
        { id: 'db',   action: 'migrateDatabase' },
      ];

      const totalSteps = steps.length;
      let currentStepIndex = 0;

      for (const step of steps) {
        updateStepUI(step.id, 'loading');

        try {
          const formData = new FormData();
          formData.append('ajax', '1');
          formData.append('action', step.action);

          const response = await fetch(hpAjaxController, {
            method: 'POST',
            body: formData
          });

          if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);

          const result = await response.json();

          if (!result.success) throw new Error(result.message || 'Erreur inconnue');

          updateStepUI(step.id, 'success');

          currentStepIndex++;
          const percent = (currentStepIndex / totalSteps) * 100;
          progressBar.style.width = `${percent}%`;

        } catch (error) {
          updateStepUI(step.id, 'error', error.message);
          console.error(error);
          migrationFinalError.style.display = 'block';

          return;
        }
      }

      progressBar.classList.remove('bg-info');
      progressBar.classList.add('bg-success');
      alertSuccess.style.display = 'block';

      setTimeout(() => {
        window.location.reload();
      }, 5000);
    });
  }

  if (btnMigrationDoneError) {
    btnMigrationDoneError.addEventListener('click', function() {
      btnMigrationDoneError.disabled = true;
      window.location.reload();
    });
  }

  function updateStepUI(stepId, status, message = '') {
    const li = document.querySelector(`li[data-step="${stepId}"]`);
    if (!li) return;

    const icon = li.querySelector('.step-icon');
    const badge = li.querySelector('.step-status');

    // Reset des classes
    li.classList.remove('list-group-item-success', 'list-group-item-danger', 'list-group-item-light');
    icon.className = 'material-icons step-icon';
    badge.className = 'badge step-status';

    if (status === 'loading') {
      icon.innerText = 'refresh';
      icon.classList.add('text-primary', 'spin-icon');
      badge.classList.add('badge-primary');
      badge.innerText = hpMigrationTranslations.statusProcessing;
    }
    else if (status === 'success') {
      icon.innerText = 'check_circle';
      icon.classList.add('text-success');
      badge.classList.add('badge-success');
      badge.innerText = hpMigrationTranslations.statusDone;
      li.classList.add('list-group-item-light');
    }
    else if (status === 'error') {
      icon.innerText = 'error';
      icon.classList.add('text-danger');
      badge.classList.add('badge-danger');
      badge.innerText = hpMigrationTranslations.statusError;

      const errorDiv = document.createElement('div');
      errorDiv.className = 'small text-danger w-100 mt-1';
      errorDiv.innerText = message;
      li.appendChild(errorDiv);
    }
  }
});
