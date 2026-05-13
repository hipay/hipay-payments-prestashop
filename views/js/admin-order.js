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

class HiPayTransactionManager {
  constructor() {
    this.elements = {};
    this.isLoading = false;

    if (typeof hipayData === 'undefined' || !hipayData.idHiPayOrder) {
      return;
    }

    this.config = hipayData;
    this.init();
  }

  /**
   * Initialize the transaction manager
   */
  init() {
    this.cacheElements();
    this.bindEvents();
    this.initOperationsBlock();
    this.loadTransactionDetails();
  }

  /**
   * Cache frequently used DOM elements
   */
  cacheElements() {
    this.elements = {
      overlay: document.getElementById('js-hipay-overlay'),
      genericMessage: document.getElementById('js-hipay-generic-message'),
      transactionContent: document.getElementById('js-hipay-transaction-content'),
      adminOrderBlock: document.getElementById('js-hipay-admin-order'),
      modalOperations: document.getElementById('js-hipay-modal-operations')
    };

    // Validate required elements
    const requiredElements = ['overlay', 'genericMessage', 'transactionContent'];
    for (const elementKey of requiredElements) {
      if (!this.elements[elementKey]) {
        console.error(`HiPay: Required element ${elementKey} not found`);
      }
    }
  }

  /**
   * Show loading overlay
   */
  showOverlay() {
    if (this.elements.overlay) {
      this.elements.overlay.classList.remove('hidden');
      this.isLoading = true;
    }
  }

  /**
   * Hide loading overlay
   */
  hideOverlay() {
    if (this.elements.overlay) {
      this.elements.overlay.classList.add('hidden');
      this.isLoading = false;
    }
  }

  /**
   * Initialize operations block UI
   */
  initOperationsBlock() {
    this.showOverlay();
    if (this.elements.genericMessage) {
      this.elements.genericMessage.classList.add('hidden');
    }
  }

  /**
   * Display generic error message
   */
  displayGenericMessage() {
    if (this.elements.genericMessage) {
      this.elements.genericMessage.classList.remove('hidden');
    }
  }

  /**
   * Create and send AJAX request
   * @param {string} url - Request URL
   * @param {FormData} formData - Form data to send
   * @returns {Promise} Fetch promise
   */
  async sendRequest(url, formData) {
    try {
      const response = await fetch(url, {
        method: 'POST',
        body: formData
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      return await response.json();
    } catch (error) {
      console.error('HiPay request failed:', error);
      throw error;
    }
  }

  /**
   * Get transaction details from server
   * @returns {Promise} Transaction details
   */
  async getTransactionDetails() {
    const formData = new FormData();
    formData.append('data[idHiPayOrder]', this.config.idHiPayOrder);
    formData.append('ajax', 'true');
    formData.append('action', 'getTransactionDetails');

    return this.sendRequest(this.config.hipayAjaxController, formData);
  }

  /**
   * Process operation (capture, refund, etc.)
   * @param {string} operationType - Type of operation
   * @param {string|number} amount - Amount for the operation
   * @returns {Promise} Operation result
   */
  async processOperation(operationType, amount) {
    const formData = new FormData();
    formData.append('data[operationType]', operationType);
    formData.append('data[idHiPayOrder]', this.config.idHiPayOrder);
    formData.append('data[amount]', amount);
    formData.append('ajax', 'true');
    formData.append('action', 'processOperation');

    return this.sendRequest(this.config.hipayAjaxTransactionController, formData);
  }

  /**
   * Load and display transaction details
   */
  async loadTransactionDetails() {
    try {
      const result = await this.getTransactionDetails();

      if (this.elements.transactionContent && result.html_data) {
        this.elements.transactionContent.innerHTML = result.html_data;
      }
    } catch (error) {
      console.error('Failed to load transaction details:', error);
      this.displayGenericMessage();
    } finally {
      this.hideOverlay();
    }
  }

  /**
   * Get modal content for operations
   * @param {HTMLElement} modalContent - Modal content element
   * @param {Object} buttonData - Button data attributes
   */
  async getOperationModalContent(modalContent, buttonData) {
    const formData = new FormData();
    const modalType = buttonData.modalType;

    formData.append('data[idHiPayOrder]', this.config.idHiPayOrder);
    formData.append('data[idOrder]', this.config.idOrder);
    formData.append('ajax', 'true');
    formData.append('action', 'getOperationsModalContent');

    Object.entries(buttonData).forEach(([key, value]) => {
      formData.append(`data[${key}]`, value);
    });

    try {
      const result = await this.sendRequest(this.config.hipayAjaxController, formData);

      if (result.html_data && modalContent) {
        modalContent.innerHTML = result.html_data;
        modalContent.querySelectorAll('input[data-decimals]').forEach(input => {
          if (input.value) {
            hipayFormatAmountComplete(input);
          }
        });
      }
    } catch (error) {
      console.error('Error loading modal content:', error);
    }
  }

  /**
   * Reset modal content
   * @param {HTMLElement} modalContent - Modal content element
   */
  async resetModalContent(modalContent) {
    const formData = new FormData();
    formData.append('ajax', 'true');
    formData.append('action', 'resetOperationsModal');

    try {
      const result = await this.sendRequest(this.config.hipayAjaxController, formData);

      if (result.html_data && modalContent) {
        modalContent.innerHTML = result.html_data;
      }
    } catch (error) {
      console.error('Error resetting modal content:', error);
    }
  }

  /**
   * Handle full capture button click
   * @param {Event} event - Click event
   */
  async handleFullCapture(event) {
    if (this.isLoading) return;

    const target = event.target;
    const amountCapturable = target.getAttribute('data-amount-capturable');

    this.showOverlay();

    try {
      const result = await this.processOperation('full-capture', amountCapturable);

      if (this.elements.transactionContent && result.html_data) {
        this.elements.transactionContent.innerHTML = result.html_data;
      }
    } catch (error) {
      console.error('Capture failed:', error);
      this.displayGenericMessage();
    } finally {
      this.hideOverlay();
    }
  }

  /**
   * Handle modal form submission
   * @param {Event} event - Submit event
   */
  async handleModalFormSubmit(event) {
    event.preventDefault();

    const form = event.target;
    const modal = this.elements.modalOperations;
    const submitButton = form.querySelector('button[type="submit"]');
    const formInputs = form.querySelectorAll('input, select, textarea');

    // Extract operation data from form
    const formData = new FormData(form);
    const operationType = formData.get('data[operationType]');
    const amount = formData.get('data[amount]') || 0;

    if (!operationType) {
      console.error('Operation type not found in form');
      return;
    }

    // Disable form elements
    if (submitButton) submitButton.disabled = true;
    formInputs.forEach(input => input.disabled = true);

    this.showOverlay();
    this.hideModal(modal);

    try {
      const result = await this.processOperation(operationType, amount);

      if (this.elements.transactionContent && result.html_data) {
        this.elements.transactionContent.innerHTML = result.html_data;
      }
    } catch (error) {
      console.error('Form submission error:', error);
      this.enableFormElements(submitButton, formInputs);
    } finally {
      this.hideOverlay();
    }
  }

  /**
   * Enable form elements
   * @param {HTMLElement} submitButton - Submit button
   * @param {NodeList} formInputs - Form inputs
   */
  enableFormElements(submitButton, formInputs) {
    if (submitButton) submitButton.disabled = false;
    formInputs.forEach(input => input.disabled = false);
  }

  /**
   * Hide modal (Bootstrap compatible)
   * @param {HTMLElement} modal - Modal element
   */
  hideModal(modal) {
    if (typeof $ !== 'undefined' && $.fn.modal) {
      $(modal).modal('hide');
    } else if (modal && modal.classList) {
      modal.classList.remove('show');
      modal.style.display = 'none';
    }
  }

  /**
   * Bind event listeners
   */
  bindEvents() {
    // Full capture event
    if (this.elements.adminOrderBlock) {
      this.elements.adminOrderBlock.addEventListener('click', (event) => {
        if (event.target.matches('#js-hipay-full-capture')) {
          this.handleFullCapture(event);
        }
      });
    }

    // Modal events (jQuery/Bootstrap compatible)
    if (typeof $ !== 'undefined' && this.elements.modalOperations) {
      const $modal = $(this.elements.modalOperations);

      $modal
        .on('shown.bs.modal', (event) => {
          const button = event.relatedTarget;
          const modalContent = this.elements.modalOperations.querySelector('.js-hipay-modal-content');

          if (button && modalContent) {
            this.getOperationModalContent(modalContent, { ...button.dataset });
          }
        })
        .on('hidden.bs.modal', () => {
          const modalContent = this.elements.modalOperations.querySelector('.js-hipay-modal-content');
          if (modalContent) {
            this.resetModalContent(modalContent);
          }
        })
        .on('submit', 'form', (event) => {
          this.handleModalFormSubmit(event);
        });
    } else {
      console.warn('HiPay: jQuery not available - modal events will not work properly');
    }
  }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  new HiPayTransactionManager();
});
