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

class HiPayFormManager {
  constructor() {
    // Form references
    this.forms = {
      account: null,
      mainSettings: null,
      cardPaymentSettings: null,
      cards: null,
      otherPMSettings: null
    };

    // State tracking
    this.state = {
      demoModeEnabled: false,
      selectedEnv: null,
      selectedDisplayMode: null,
      oneClickEnabled: false,
      hostedPageEnabled: false,
      cpmSpecificsEnabled: new Map()
    };

    // CSS selectors configuration
    this.selectors = {
      forms: {
        account: '.js-hipay-account-form',
        mainSettings: '.js-hipay-main-settings-form',
        cardPaymentSettings: '.js-hipay-card-payment-form',
        cards: '.js-hipay-cards-form',
        otherPMSettings: '.js-hipay-other-pm-form'
      },
      blocks: {
        envProd: '#js-hipay-env-prod-block',
        envTest: '#js-hipay-env-test-block',
        displayModeHostedPage: '#js-hipay-mode-hosted-page-block',
        displayModeHostedFields: '#js-hipay-mode-hosted-fields-block',
        oneClickEnabled: '.js-hipay-one-click-enabled-block',
        enableDemoMode: '.js-hipay-enable-demo-mode-block',
        enableOneClick: '.js-hipay-one-click-block',
        hostedPageOptions: '#js-hipay-hosted-page-options-block',
        hostedPageDisplayModeRadio: '.js-hp-mode-hosted-page-radio',
        hostedPageDisabledMessage: '.js-hp-hosted-page-disabled-message',
        hostedPagePM: '.js-hipay-hosted-page-pm-block',
        hostedPageDisplay: '.js-hipay-hosted-page-display-block',
        cardHostedPageType: '.js-hp-card-hostedpage-type-block',
        cardCancelButton: '.js-hp-card-cancelbutton-block',
        cardThreeDS: '.js-hp-card-threeds-block',
        cardHostedPageInheritedMessage: '.js-hp-card-hostedpage-inherited-message'
      },
      inputs: {
        environment: 'input[name="hpAccountSettings[environment]"]',
        demoMode: 'input[name="hpAccountSettings[useDemoMode]"]',
        displayMode: 'input[name="hpCardPaymentSettings[displayMode]"]',
        oneClick: 'input[name="hpCardPaymentSettings[oneClickEnabled]"]',
        hostedPageEnabled: 'input[name="hpMainSettings[hostedPageEnabled]"]',
        apmChannel: 'input[name*="[channel]"]',
        hostedFieldsDisplayMode: '#hp-mode-hosted-fields',
        hostedPageDisplayMode: '#hp-mode-hosted-page',
        environmentDisableable: '.js-hp-environment-radio-block input',
        environmentReadOnlyable: '#js-hipay-env-test-block input, #js-hipay-env-prod-block input',
        hostedPageLangInput: '.hipay-lang-input',
        hostedPageLangBtn: '.js-hipay-lang-btn',
        cardHostedPageType: 'input[name="hpCardPaymentSettings[hostedPageType]"]',
        cardCancelButtonDisplayed: 'input[name="hpCardPaymentSettings[cancelButtonDisplayed]"]',
        cardThreeDSMode: '#hpCardPaymentSettings_threeDSMode',
        mainHostedPageType: 'input[name="hpMainSettings[hostedPageType]"]',
        mainCancelButtonDisplayed: 'input[name="hpMainSettings[cancelButtonDisplayed]"]',
        mainThreeDSMode: '#hpMainSettings_threeDSMode'
      },
      switches: {
        demoMode: '.js-hipay-enable-demo-mode-switch',
        oneClick: '.js-hipay-one-click-switch',
        cpmSpecifics: '.js-hipay-cpm-specifics-switch',
        hostedPage: '.js-hipay-hosted-page-switch'
      },
      orderable: {
        panels: '.js-panel-orderable',
        chevronUp: '.icon-chevron-up',
        chevronDown: '.icon-chevron-down',
        positionSpan: '.js-hipay-position',
        positionInput: 'input[name*="[position]"]'
      },
      modals: {
        healthCheck: '#js-hipay-payments-health-check-modal',
      }
    };

    this.draggedPanel = null;
  }

  /**
   * Initialize the form manager
   */
  init() {
    try {
      this.initializeForms();
      this.initializeEnvironmentHandling();
      this.initializeDemoModeHandling();
      this.initializeDisplayModeHandling();
      this.initializeOneClickHandling();
      this.initializeHostedPageHandling();
      this.initializeHostedPageDisplayHandling();
      this.initializeCPMHandling();
      this.initializeOrderableHandling();
      this.initializeModalsHandling();

      console.log('HiPay form manager initialized successfully');
    } catch (error) {
      console.error('Failed to initialize HiPay form manager:', error);
    }
  }

  /**
   * Get and validate all required forms
   */
  initializeForms() {
    Object.keys(this.forms).forEach(formKey => {
      const selector = this.selectors.forms[formKey];
      this.forms[formKey] = document.querySelector(selector);

      if (!this.forms[formKey]) {
        console.warn(`Form not found: ${selector}`);
      }
    });
  }

  /**
   * Safely toggle element visibility
   * @param {HTMLElement|null} element - Element to toggle
   * @param {boolean} show - Whether to show the element
   */
  toggleElementVisibility(element, show) {
    if (element) {
      element.style.display = show ? 'block' : 'none';
    }
  }

  /**
   * Get checked radio button value safely
   * @param {HTMLElement} container - Container to search in
   * @param {string} selector - Radio button selector
   * @returns {string|null} - Value of checked radio or null
   */
  getCheckedRadioValue(container, selector) {
    if (!container) return null;

    const checkedRadio = container.querySelector(`${selector}:checked`);
    return checkedRadio ? checkedRadio.getAttribute('data-value') || checkedRadio.value : null;
  }

  /**
   * Toggle environment-specific blocks
   * @param {string} selectedEnv - Selected environment ('prod' or 'test')
   */
  toggleEnvironment(selectedEnv) {
    if (!this.forms.account) return;

    const prodBlock = this.forms.account.querySelector(this.selectors.blocks.envProd);
    const testBlock = this.forms.account.querySelector(this.selectors.blocks.envTest);

    this.toggleElementVisibility(prodBlock, selectedEnv === 'prod');
    this.toggleElementVisibility(testBlock, selectedEnv !== 'prod');

    this.state.selectedEnv = selectedEnv;
  }

  /**
   * Toggle demo mode and disable/enable relevant inputs
   * @param {boolean} demoModeEnabled - Whether demo mode is enabled
   */
  toggleDemoMode(demoModeEnabled) {
    const disableAbleInputs = document.querySelectorAll(this.selectors.inputs.environmentDisableable);
    const readOnlyAbleInputs = document.querySelectorAll(this.selectors.inputs.environmentReadOnlyable);

    readOnlyAbleInputs.forEach(input => {
      input.readOnly = demoModeEnabled;
    });
    disableAbleInputs.forEach(input => {
      input.disabled = demoModeEnabled;
    });

    this.state.demoModeEnabled = demoModeEnabled;
  }

  /**
   * Toggle display mode blocks
   * @param {string} selectedDisplayMode - Selected display mode
   */
  toggleDisplayMode(selectedDisplayMode) {
    if (!this.forms.cardPaymentSettings) return;

    const hostedPageBlock = this.forms.cardPaymentSettings.querySelector(this.selectors.blocks.displayModeHostedPage);
    const hostedFieldsBlock = this.forms.cardPaymentSettings.querySelector(this.selectors.blocks.displayModeHostedFields);

    this.toggleElementVisibility(hostedPageBlock, selectedDisplayMode === 'hosted_page');
    this.toggleElementVisibility(hostedFieldsBlock, selectedDisplayMode !== 'hosted_page');

    this.state.selectedDisplayMode = selectedDisplayMode;

    this.toggleCardHostedPageInheritance(this.state.hostedPageEnabled && selectedDisplayMode === 'hosted_page');
  }

  /**
   * @param {boolean} inherited
   */
  toggleCardHostedPageInheritance(inherited) {
    if (!this.forms.cardPaymentSettings || !this.forms.mainSettings) return;

    const cardHostedPageTypeInputs = this.forms.cardPaymentSettings.querySelectorAll(this.selectors.inputs.cardHostedPageType);
    const cardCancelButtonInputs = this.forms.cardPaymentSettings.querySelectorAll(this.selectors.inputs.cardCancelButtonDisplayed);
    const cardThreeDSSelect = this.forms.cardPaymentSettings.querySelector(this.selectors.inputs.cardThreeDSMode);

    cardHostedPageTypeInputs.forEach(input => { input.disabled = inherited; });
    cardCancelButtonInputs.forEach(input => { input.disabled = inherited; });
    if (cardThreeDSSelect) cardThreeDSSelect.disabled = inherited;

    [
      this.selectors.blocks.cardHostedPageType,
      this.selectors.blocks.cardCancelButton,
      this.selectors.blocks.cardThreeDS
    ].forEach(selector => {
      const block = this.forms.cardPaymentSettings.querySelector(selector);
      if (!block) return;
      block.style.opacity = inherited ? '0.5' : '';
    });

    this.forms.cardPaymentSettings.querySelectorAll(this.selectors.blocks.cardHostedPageInheritedMessage).forEach(message => {
      this.toggleElementVisibility(message, inherited);
    });

    if (!inherited) return;

    const mainHostedPageType = this.getCheckedRadioValue(this.forms.mainSettings, this.selectors.inputs.mainHostedPageType);
    if (mainHostedPageType !== null) {
      cardHostedPageTypeInputs.forEach(input => {
        input.checked = (input.getAttribute('data-value') || input.value) === mainHostedPageType;
      });
    }

    const mainCancelButtonValue = this.getCheckedRadioValue(this.forms.mainSettings, this.selectors.inputs.mainCancelButtonDisplayed);
    if (mainCancelButtonValue !== null) {
      cardCancelButtonInputs.forEach(input => {
        input.checked = input.value === mainCancelButtonValue;
      });
    }

    const mainThreeDSSelect = this.forms.mainSettings.querySelector(this.selectors.inputs.mainThreeDSMode);
    if (cardThreeDSSelect && mainThreeDSSelect) {
      cardThreeDSSelect.value = mainThreeDSSelect.value;
    }
  }

  /**
   * Toggle one-click payment block
   * @param {boolean} oneClickEnabled - Whether one-click is enabled
   */
  toggleOneClick(oneClickEnabled) {
    const oneClickBlock = document.querySelector(this.selectors.blocks.oneClickEnabled);
    this.toggleElementVisibility(oneClickBlock, oneClickEnabled);
    this.state.oneClickEnabled = oneClickEnabled;
  }

  /**
   * @param {boolean} hostedPageEnabled
   */
  toggleHostedPage(hostedPageEnabled) {
    const optionsBlock = document.querySelector(this.selectors.blocks.hostedPageOptions);
    this.toggleElementVisibility(optionsBlock, hostedPageEnabled);
    this.state.hostedPageEnabled = hostedPageEnabled;
    this.toggleCardPaymentModeAvailability(hostedPageEnabled);
    this.toggleAPMChannelAvailability(hostedPageEnabled);

    const displayBlock = document.querySelector(this.selectors.blocks.hostedPageDisplay);
    this.toggleElementVisibility(displayBlock, hostedPageEnabled);

    const selectedDisplayMode = this.forms.cardPaymentSettings
      ? this.getCheckedRadioValue(this.forms.cardPaymentSettings, this.selectors.inputs.displayMode)
      : null;
    this.toggleCardHostedPageInheritance(hostedPageEnabled && selectedDisplayMode === 'hosted_page');
  }

  /**
   * @param {boolean} hostedPageEnabled
   */
  toggleAPMChannelAvailability(hostedPageEnabled) {
    if (!this.forms.otherPMSettings) return;

    const hostedPagePMBlock = this.forms.otherPMSettings.querySelector(this.selectors.blocks.hostedPagePM);
    this.toggleElementVisibility(hostedPagePMBlock, hostedPageEnabled);

    if (hostedPageEnabled) return;

    const channelRadios = this.forms.otherPMSettings.querySelectorAll(this.selectors.inputs.apmChannel);
    channelRadios.forEach(radio => {
      if (radio.value === 'hosted_fields') {
        radio.checked = true;
      } else if (radio.value === 'hosted_page') {
        radio.checked = false;
      }
    });
  }

  /**
   * @param {boolean} hostedPageEnabled
   */
  toggleCardPaymentModeAvailability(hostedPageEnabled) {
    if (!this.forms.cardPaymentSettings) return;

    const hostedPageInput = this.forms.cardPaymentSettings.querySelector(this.selectors.inputs.hostedPageDisplayMode);
    const hostedPageRadioBlock = this.forms.cardPaymentSettings.querySelector(this.selectors.blocks.hostedPageDisplayModeRadio);
    const hostedFieldsInput = this.forms.cardPaymentSettings.querySelector(this.selectors.inputs.hostedFieldsDisplayMode);
    const disabledMessage = this.forms.cardPaymentSettings.querySelector(this.selectors.blocks.hostedPageDisabledMessage);

    if (hostedPageInput) {
      hostedPageInput.disabled = !hostedPageEnabled;
      if (!hostedPageEnabled && hostedPageInput.checked && hostedFieldsInput) {
        hostedPageInput.checked = false;
        hostedFieldsInput.checked = true;
        this.toggleDisplayMode(hostedFieldsInput.getAttribute('data-value') || hostedFieldsInput.value);
      }
    }

    if (hostedPageRadioBlock) {
      hostedPageRadioBlock.classList.toggle('disabled', !hostedPageEnabled);
      hostedPageRadioBlock.style.opacity = hostedPageEnabled ? '' : '0.5';
    }

    this.toggleElementVisibility(disabledMessage, !hostedPageEnabled);
  }

  initializeHostedPageHandling() {
    if (!this.forms.mainSettings) return;

    const hostedPageRadios = this.forms.mainSettings.querySelectorAll(this.selectors.inputs.hostedPageEnabled);
    if (!hostedPageRadios.length) return;

    const readAndApply = () => {
      const checked = this.forms.mainSettings.querySelector(this.selectors.inputs.hostedPageEnabled + ':checked');
      if (checked) {
        this.toggleHostedPage(parseInt(checked.value) === 1);
      }
    };

    readAndApply();

    hostedPageRadios.forEach(radio => {
      radio.addEventListener('change', readAndApply);
    });

    const hostedPageSwitch = this.forms.mainSettings.querySelector(this.selectors.switches.hostedPage);
    if (hostedPageSwitch) {
      hostedPageSwitch.addEventListener('click', () => setTimeout(readAndApply, 10));
    }
  }

  initializeHostedPageDisplayHandling() {
    const langButtons = document.querySelectorAll(this.selectors.inputs.hostedPageLangBtn);
    if (!langButtons.length) return;

    langButtons.forEach(btn => {
      btn.addEventListener('click', (event) => {
        event.preventDefault();

        const idLang = btn.getAttribute('data-lang');

        document.querySelectorAll(this.selectors.inputs.hostedPageLangInput).forEach(input => {
          input.style.display = input.getAttribute('data-lang') === idLang ? '' : 'none';
        });

        langButtons.forEach(otherBtn => otherBtn.classList.toggle('active', otherBtn === btn));
      });
    });
  }

  /**
   * Toggle CPM (Card Payment Method) specific blocks
   * @param {string} cpmId - CPM identifier
   * @param {boolean} cpmSpecificsEnabled - Whether CPM specifics are enabled
   */
  toggleCPM(cpmId, cpmSpecificsEnabled) {
    if (!this.forms.cards) return;

    const cpmBlock = this.forms.cards.querySelector(`.js-hipay-cpm-${cpmId}-specifics-block`);
    this.toggleElementVisibility(cpmBlock, cpmSpecificsEnabled);
    this.state.cpmSpecificsEnabled.set(cpmId, cpmSpecificsEnabled);
  }

  /**
   * Initialize environment handling
   */
  initializeEnvironmentHandling() {
    if (!this.forms.account) return;

    // Set initial state
    const selectedEnv = this.getCheckedRadioValue(this.forms.account, this.selectors.inputs.environment);
    if (selectedEnv) {
      this.toggleEnvironment(selectedEnv);
    }

    // Add event listeners
    const envRadios = this.forms.account.querySelectorAll(this.selectors.inputs.environment);
    envRadios.forEach(radio => {
      radio.addEventListener('change', (e) => {
        const env = e.target.getAttribute('data-value') || e.target.value;
        this.toggleEnvironment(env);
      });
    });
  }

  /**
   * Initialize demo mode handling
   */
  initializeDemoModeHandling() {
    if (!this.forms.account) return;

    // Set initial state
    const demoModeValue = this.getCheckedRadioValue(this.forms.account, this.selectors.inputs.demoMode);
    if (demoModeValue !== null) {
      this.toggleDemoMode(parseInt(demoModeValue) === 1);
    }

    // Add event listener for demo mode switch
    const demoModeSwitch = this.forms.account.querySelector(this.selectors.switches.demoMode);
    if (demoModeSwitch) {
      demoModeSwitch.addEventListener('click', () => {
        // Small delay to ensure radio button state is updated
        setTimeout(() => {
          const enableDemoModeBlock = this.forms.account.querySelector(this.selectors.blocks.enableDemoMode);
          const updatedValue = this.getCheckedRadioValue(enableDemoModeBlock, this.selectors.inputs.demoMode);
          if (updatedValue !== null) {
            this.toggleDemoMode(parseInt(updatedValue) === 1);
          }
        }, 10);
      });
    }
  }

  /**
   * Initialize display mode handling
   */
  initializeDisplayModeHandling() {
    if (!this.forms.cardPaymentSettings) return;

    // Set initial state
    const selectedDisplayMode = this.getCheckedRadioValue(this.forms.cardPaymentSettings, this.selectors.inputs.displayMode);
    if (selectedDisplayMode) {
      this.toggleDisplayMode(selectedDisplayMode);
    }

    // Add event listeners
    const displayModeRadios = this.forms.cardPaymentSettings.querySelectorAll(this.selectors.inputs.displayMode);
    displayModeRadios.forEach(radio => {
      radio.addEventListener('change', (e) => {
        const displayMode = e.target.getAttribute('data-value') || e.target.value;
        this.toggleDisplayMode(displayMode);
      });
    });
  }

  /**
   * Initialize one-click handling
   */
  initializeOneClickHandling() {
    if (!this.forms.cardPaymentSettings) return;

    const enableOneClickBlock = this.forms.cardPaymentSettings.querySelector(this.selectors.blocks.enableOneClick);
    if (!enableOneClickBlock) return;

    // Set initial state
    const oneClickValue = this.getCheckedRadioValue(enableOneClickBlock, this.selectors.inputs.oneClick);
    if (oneClickValue !== null) {
      this.toggleOneClick(parseInt(oneClickValue) === 1);
    }

    // Add event listener for one-click switch
    const oneClickSwitch = this.forms.cardPaymentSettings.querySelector(this.selectors.switches.oneClick);
    if (oneClickSwitch) {
      oneClickSwitch.addEventListener('click', () => {
        setTimeout(() => {
          const updatedValue = this.getCheckedRadioValue(enableOneClickBlock, this.selectors.inputs.oneClick);
          if (updatedValue !== null) {
            this.toggleOneClick(parseInt(updatedValue) === 1);
          }
        }, 10);
      });
    }
  }

  /**
   * Initialize CPM handling
   */
  initializeCPMHandling() {
    if (!this.forms.cards) return;

    document.querySelectorAll('input[data-decimals]').forEach(input => {
      if (input.value) {
        hipayFormatAmountComplete(input);
      }
    });

    const cpmSwitches = this.forms.cards.querySelectorAll(this.selectors.switches.cpmSpecifics);

    cpmSwitches.forEach(switchElement => {
      const cpmId = switchElement.getAttribute('data-cpm-key');
      if (!cpmId) return;

      const cpmRadioBlock = this.forms.cards.querySelector(`.js-hipay-cpm-specifics-${cpmId}-radio-block`);
      if (!cpmRadioBlock) return;

      // Set initial state
      const cpmSelector = `input[name="hpCardPaymentSettings[paymentMethods][${cpmId}][currenciesCountriesManaged]"]`;
      const cpmValue = this.getCheckedRadioValue(cpmRadioBlock, cpmSelector);
      if (cpmValue !== null) {
        this.toggleCPM(cpmId, parseInt(cpmValue) === 1);
      }

      // Add event listener
      switchElement.addEventListener('click', () => {
        setTimeout(() => {
          const updatedValue = this.getCheckedRadioValue(cpmRadioBlock, cpmSelector);
          if (updatedValue !== null) {
            this.toggleCPM(cpmId, parseInt(updatedValue) === 1);
          }
        }, 10);
      });
    });
  }

  /**
   * Initialize orderable panels handling
   */
    /**
   * Initialize orderable panels handling
   */
  initializeOrderableHandling() {
    const panels = document.querySelectorAll(this.selectors.orderable.panels);

    if (panels.length === 0) return;

    panels.forEach(panel => {
      const chevronUp = panel.querySelector(this.selectors.orderable.chevronUp);
      const chevronDown = panel.querySelector(this.selectors.orderable.chevronDown);

      // ----- Chevrons  -----
      if (chevronUp) {
        chevronUp.style.cursor = 'pointer';
        chevronUp.addEventListener('click', () => {
          this.movePanel(panel, 'up');
          this.reindexPanels();
        });
      }

      if (chevronDown) {
        chevronDown.style.cursor = 'pointer';
        chevronDown.addEventListener('click', () => {
          this.movePanel(panel, 'down');
          this.reindexPanels();
        });
      }

      // ----- Drag & Drop HTML5 -----
      panel.setAttribute('draggable', 'true');

      panel.addEventListener('dragstart', (event) => {
        this.draggedPanel = panel;
        event.dataTransfer.effectAllowed = 'move';

        panel.classList.add('hipay-panel-dragging');
      });

      panel.addEventListener('dragend', () => {
        this.draggedPanel = null;
        panel.classList.remove('hipay-panel-dragging');

        document.querySelectorAll('.hipay-panel-drag-over').forEach(p => {
          p.classList.remove('hipay-panel-drag-over');
        });
      });

      panel.addEventListener('dragover', (event) => {
        event.preventDefault();
        if (panel !== this.draggedPanel) {
          panel.classList.add('hipay-panel-drag-over');
        }
        event.dataTransfer.dropEffect = 'move';
      });

      panel.addEventListener('dragleave', () => {
        panel.classList.remove('hipay-panel-drag-over');
      });

      panel.addEventListener('drop', (event) => {
        event.preventDefault();

        panel.classList.remove('hipay-panel-drag-over');

        if (!this.draggedPanel || this.draggedPanel === panel) {
          return;
        }

        const container = panel.parentNode;
        const panelsArray = Array.from(container.querySelectorAll(this.selectors.orderable.panels));

        const draggedIndex = panelsArray.indexOf(this.draggedPanel);
        const targetIndex = panelsArray.indexOf(panel);

        if (draggedIndex === -1 || targetIndex === -1) {
          return;
        }

        if (draggedIndex < targetIndex) {
          container.insertBefore(this.draggedPanel, panel.nextSibling);
        } else {
          container.insertBefore(this.draggedPanel, panel);
        }

        this.reindexPanels();
      });
    });
  }

  initializeModalsHandling() {
    const healthCheckModal = document.querySelector(this.selectors.modals.healthCheck);
    if (!healthCheckModal) {
      return;
    }

    $(healthCheckModal).on('show.bs.modal', async () => {
      const modalContent = healthCheckModal.querySelector('.js-hipay-modal-body');

      const isLoaded = healthCheckModal.getAttribute('data-loaded') === 'true';
      const isLoading = healthCheckModal.classList.contains('is-loading');

      if (modalContent && !isLoaded && !isLoading) {
        await this.getHealthCheckModalContent(healthCheckModal, modalContent);
      }
    });
  }

  async getHealthCheckModalContent(modalContainer, modalContent) {
    const errorMsg = modalContainer.querySelector('.js-hipay-generic-error');

    modalContainer.classList.add('is-loading');
    if (errorMsg) {
      errorMsg.style.display = 'none';
    }

    const formData = new FormData();
    formData.append('ajax', 'true');
    formData.append('action', 'getHealthCheckModalContent');

    try {
      const result = await sendRequest(hpAjaxController, formData);

      if (result.html_data) {
        modalContent.innerHTML = result.html_data;
        modalContainer.setAttribute('data-loaded', 'true');
      }
    } catch (error) {
      console.error('Error loading modal content:', error);

      if (errorMsg) {
        errorMsg.style.display = 'block';
        modalContent.style.display = 'none'; // Hide the loader if it failed
      }
    } finally {
      modalContainer.classList.remove('is-loading');
    }
  }

  /**
   * Re-index all orderable panels based on their current DOM order
   */
  reindexPanels() {
    const panels = Array.from(document.querySelectorAll(this.selectors.orderable.panels));
    panels.forEach((panel, index) => {
      const newPosition = index + 1;
      this.updatePanelPosition(panel, newPosition);
    });
  }

  /**
   * Move panel up or down
   * @param {HTMLElement} panel - Panel to move
   * @param {string} direction - Direction ('up' or 'down')
   */
  movePanel(panel, direction) {
    const currentPosition = parseInt(panel.getAttribute('data-position'));
    let targetPanel;

    if (direction === 'up') {
      targetPanel = this.findPanelByPosition(currentPosition - 1);
      if (!targetPanel) return; // Déjà en première position
    } else {
      targetPanel = this.findPanelByPosition(currentPosition + 1);
      if (!targetPanel) return; // Déjà en dernière position
    }

    this.swapPositions(panel, targetPanel);

    if (direction === 'up') {
      panel.parentNode.insertBefore(panel, targetPanel);
    } else {
      panel.parentNode.insertBefore(targetPanel, panel);
    }
  }

  /**
   * Find panel by position value
   * @param {number} position - Position to find
   * @returns {HTMLElement|null} - Panel element or null
   */
  findPanelByPosition(position) {
    const panels = document.querySelectorAll(this.selectors.orderable.panels);
    for (let panel of panels) {
      if (parseInt(panel.getAttribute('data-position')) === position) {
        return panel;
      }
    }
    return null;
  }

  /**
   * Swap positions between two panels
   * @param {HTMLElement} panel1 - First panel
   * @param {HTMLElement} panel2 - Second panel
   */
  swapPositions(panel1, panel2) {
    const pos1 = parseInt(panel1.getAttribute('data-position'));
    const pos2 = parseInt(panel2.getAttribute('data-position'));

    this.updatePanelPosition(panel1, pos2);
    this.updatePanelPosition(panel2, pos1);
  }

  /**
   * Update panel position attributes and values
   * @param {HTMLElement} panel - Panel to update
   * @param {number} newPosition - New position value
   */
  updatePanelPosition(panel, newPosition) {
    panel.setAttribute('data-position', newPosition);

    const positionSpan = panel.querySelector(this.selectors.orderable.positionSpan);
    if (positionSpan) {
      positionSpan.textContent = newPosition;
    }

    const hiddenInput = panel.querySelector(this.selectors.orderable.positionInput);
    if (hiddenInput) {
      hiddenInput.value = newPosition;
    }
  }

  /**
   * Get current state for debugging
   * @returns {Object} Current state
   */
  getState() {
    return {
      ...this.state,
      cpmSpecificsEnabled: Object.fromEntries(this.state.cpmSpecificsEnabled)
    };
  }
}

// Initialize the form manager when DOM is ready
function initializeHiPayForms() {
  const formManager = new HiPayFormManager();
  formManager.init();

  // Make available globally for debugging
  window.hiPayFormManager = formManager;
}

// Auto-initialize
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initializeHiPayForms);
} else {
  initializeHiPayForms();
}