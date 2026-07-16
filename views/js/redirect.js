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

// Configuration constants
const CONFIG = {
    POLL_INTERVAL: 3000,
    MAX_ATTEMPTS: 10,
    TIMEOUT_MESSAGE_ID: 'js-hipay-timeout-message',
    LOADER_ID: 'js-hipay-loader'
};

const BANCOMAT_CONFIG = {
    POLL_INTERVAL: 10000,
    MAX_ATTEMPTS: 30,
};

/**
 * Makes a request to check HiPay order status and get redirect URL
 * @param {boolean} isTimeout - Whether this is a timeout request
 * @returns {Promise<Object>} Response containing redirectUrl if available
 */
async function hipayRedirect(isTimeout = false) {
  try {
    // Clean the controller URL
    const controller = hipayRedirectController.replace(/&amp;/g, '&');

    // Prepare form data
    const formData = new FormData();
    formData.append('ajax', 'true');
    formData.append('token', hipayCustomerToken);
    formData.append('hipayOrderId', hipayOrderId);
    formData.append('hipayTransactionReference', hipayTransactionReference);
    formData.append('idCart', idCart);

    if (isTimeout) {
      formData.append('timeout', 'true');
    }

    const response = await fetch(controller, {
      method: 'POST',
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();
    return data;

  } catch (error) {
    console.error('HiPay redirect error:', error);
    throw error;
  }
}

/**
 * Updates UI elements visibility
 * @param {string} elementId - Element ID to show/hide
 * @param {boolean} show - Whether to show the element
 */
function toggleElement(elementId, show) {
  const element = document.getElementById(elementId);
  if (element) {
    element.style.display = show ? 'block' : 'none';
  }
}

/**
 * Handles successful redirect response
 * @param {Object} result - Response from hipayRedirect
 */
function handleRedirectResponse(result) {
  if (result && result.redirectUrl) {
    window.top.location.href = result.redirectUrl;
    return true;
  }
  return false;
}

/**
 * Handles timeout scenario
 */
async function handleTimeout() {
  console.warn('HiPay polling timeout reached');

  if (typeof paymentProduct !== 'undefined' && paymentProduct === 'bancomatpay') {
    showBancomatState('timeout');
    return;
  }

  toggleElement(CONFIG.TIMEOUT_MESSAGE_ID, true);
  toggleElement(CONFIG.LOADER_ID, false);

  try {
    const result = await hipayRedirect(true);
    handleRedirectResponse(result);
  } catch (error) {
    console.error('Timeout redirect failed:', error);
  }
}

/**
 * Polls HiPay service for order status with automatic timeout handling
 * @param {Function} callback - Function to call on each poll
 * @param {number} delay - Delay between polls in milliseconds
 * @param {number} maxAttempts - Maximum number of polling attempts
 */
function startPolling(callback, delay, maxAttempts) {
  let attemptCount = 0;

  const poll = async () => {
    try {
      await callback();
    } catch (error) {
      console.error(`Polling attempt ${attemptCount + 1} failed:`, error);
    }

    attemptCount++;

    if (attemptCount >= maxAttempts) {
      handleTimeout();
    } else {
      setTimeout(poll, delay);
    }
  };

  // Start first poll
  poll();
}

/**
 * Initializes HiPay order checking if conditions are met
 */
function initializeHiPayCheck() {
  if (window !== window.top) {
    top.location.href = window.location.href;
  }

  startPolling(
    async () => {
      const result = await hipayRedirect();
      const redirected = handleRedirectResponse(result);

      if (redirected) {
        console.log('HiPay redirect successful');
      }
    },
    CONFIG.POLL_INTERVAL,
    CONFIG.MAX_ATTEMPTS
  );
}

/**
 * Shows the given BancomatPay state and hides the others.
 * @param {string} state - 'pending' | 'success' | 'failed' | 'timeout'
 */
function showBancomatState(state) {
  document.querySelectorAll('.js-hipay-bancomat-state').forEach(function (el) {
    el.style.display = 'none';
  });
  const target = document.querySelector('.js-hipay-bancomat-state--' + state);
  if (target) {
    target.style.display = '';
  }
}

/**
 * Polls the checkBancomatPayStatus endpoint until the order is confirmed or failed.
 */
async function pollBancomatPayStatus() {
  const formData = new FormData();
  formData.append('action', 'checkBancomatPayStatus');
  formData.append('token', hipayCustomerToken);
  formData.append('idCart', idCart);
  formData.append('cartSecureKey', cartSecureKey);

  try {
    const response = await fetch(hipayPaymentControllerUrl, {
      method: 'POST',
      body: formData,
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    });
    if (!response.ok) {
      return;
    }
    const data = await response.json();
    if (!data.success) {
      return;
    }
    if (data.status === 'success') {
      showBancomatState('success');
      setTimeout(function () {
        window.location.href = data.redirectUrl;
      }, 2000);
    } else if (data.status === 'failed') {
      showBancomatState('failed');
    }
    // 'pending' → keep polling
  } catch (e) {
    // network error — keep polling
  }
}

/**
 * Starts the BancomatPay-specific polling loop.
 */
function initializeBancomatPayCheck() {
  startPolling(
    pollBancomatPayStatus,
    BANCOMAT_CONFIG.POLL_INTERVAL,
    BANCOMAT_CONFIG.MAX_ATTEMPTS
  );
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', function () {
    if (typeof paymentProduct !== 'undefined' && paymentProduct === 'bancomatpay') {
      initializeBancomatPayCheck();
    } else {
      initializeHiPayCheck();
    }
  });
} else {
  if (typeof paymentProduct !== 'undefined' && paymentProduct === 'bancomatpay') {
    initializeBancomatPayCheck();
  } else {
    initializeHiPayCheck();
  }
}
