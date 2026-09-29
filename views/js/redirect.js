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

const PENDING_FLOW_PRODUCTS = ['bancomatpay', 'bizum'];

const PENDING_FLOW_CONFIG = {
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

  if (typeof paymentProduct !== 'undefined' && PENDING_FLOW_PRODUCTS.includes(paymentProduct)) {
    showPendingPaymentState('timeout');
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
 * @returns {{stop: Function}}
 */
function startPolling(callback, delay, maxAttempts) {
  let attemptCount = 0;
  let stopped = false;
  let timeoutId = null;

  const stop = () => {
    stopped = true;
    if (timeoutId) {
      clearTimeout(timeoutId);
    }
  };

  const poll = async () => {
    if (stopped) {
      return;
    }

    let shouldStop = false;
    try {
      shouldStop = await callback();
    } catch (error) {
      console.error(`Polling attempt ${attemptCount + 1} failed:`, error);
    }

    if (stopped || shouldStop) {
      return;
    }

    attemptCount++;

    if (attemptCount >= maxAttempts) {
      handleTimeout();
    } else {
      timeoutId = setTimeout(poll, delay);
    }
  };

  // Start first poll
  poll();

  return { stop };
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

      return redirected;
    },
    CONFIG.POLL_INTERVAL,
    CONFIG.MAX_ATTEMPTS
  );
}

function showPendingPaymentState(state) {
  document.querySelectorAll('.js-hipay-pending-payment-state').forEach(function (el) {
    el.style.display = 'none';
  });
  const target = document.querySelector('.js-hipay-pending-payment-state--' + state);
  if (target) {
    target.style.display = '';
  }
}

async function pollPendingPaymentStatus() {
  const formData = new FormData();
  formData.append('action', 'checkPendingPaymentStatus');
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
      return false;
    }
    const data = await response.json();
    if (!data.success) {
      return false;
    }
    if (data.status === 'success') {
      showPendingPaymentState('success');
      setTimeout(function () {
        window.location.href = data.redirectUrl;
      }, 2000);
      return true;
    }
    if (data.status === 'failed') {
      showPendingPaymentState('failed');
      return true;
    }
  } catch (e) {
    console.error('HiPay pending payment status poll failed:', e);
  }

  return false;
}

function initializePendingPaymentCheck() {
  startPolling(
    pollPendingPaymentStatus,
    PENDING_FLOW_CONFIG.POLL_INTERVAL,
    PENDING_FLOW_CONFIG.MAX_ATTEMPTS
  );
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', function () {
    if (typeof paymentProduct !== 'undefined' && PENDING_FLOW_PRODUCTS.includes(paymentProduct)) {
      initializePendingPaymentCheck();
    } else {
      initializeHiPayCheck();
    }
  });
} else {
  if (typeof paymentProduct !== 'undefined' && PENDING_FLOW_PRODUCTS.includes(paymentProduct)) {
    initializePendingPaymentCheck();
  } else {
    initializeHiPayCheck();
  }
}
