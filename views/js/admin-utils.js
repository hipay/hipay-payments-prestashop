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

function hipayFormatAmountComplete(input) {
  const decimals = parseInt(input.dataset.decimals) || 2;
  let value = input.value.replace(/,/g, '.');

  if (value && !isNaN(parseFloat(value))) {
    input.value = parseFloat(value).toFixed(decimals);
  }
}

/**
 * Create and send AJAX request
 * @param {string} url - Request URL
 * @param {FormData} formData - Form data to send
 * @returns {Promise} Fetch promise
 */
async function sendRequest(url, formData) {
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
