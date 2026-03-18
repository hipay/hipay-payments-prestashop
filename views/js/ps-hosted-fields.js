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

(async function initializeHiPay() {
  if (typeof window.PSHiPayData === 'undefined') {
    console.log('PSHiPayData not found, skipping HiPay initialization');
    return;
  }

  try {
    // Fetch the integrity hash
    const response = await fetch('https://libs.hipay.com/js/sdkjs.integrity');

    if (!response.ok) {
      throw new Error(`HTTP error! Status: ${response.status}`);
    }

    const integrityHash = await response.text();

    const fingerprintJS = document.createElement('script');
    fingerprintJS.src = 'https://secure-gateway.hipay-tpp.com/gateway/toolbox/fingerprint';
    document.head.appendChild(fingerprintJS);

    // Create and configure the script element
    const script = document.createElement('script');
    script.src = 'https://libs.hipay.com/js/sdkjs.js';
    script.integrity = integrityHash.trim();
    script.crossOrigin = 'anonymous';

    script.onload = () => {
      console.log('HiPay SDK loaded successfully');
      const hipayPayments = HiPay({
        username: PSHiPayData.credentials.username,
        password: PSHiPayData.credentials.password,
        environment: PSHiPayData.credentials.env,
        lang: prestashop.language.iso_code,
      });

      const hipayPaymentsConfig = {};
      const hipayPaymentsInstances = {};

      if (!Array.isArray(PSHiPayData.cardSpecifics) || PSHiPayData.cardSpecifics.length) {
        hipayPaymentsConfig['card'] = {
          selector: 'js-hipay-payments-hosted-fields-form-card',
          template: 'auto',
          brand: PSHiPayData.cardSpecifics.paymentMethodsCodes,
          one_click: {
            enabled: PSHiPayData.cardSpecifics.oneClickEnabled,
          },
          fields: {
            cardHolder: {
              defaultFirstname: prestashop.customer.firstname,
              defaultLastname: prestashop.customer.lastname
            }
          },
          styles: {
            base: {
              color: PSHiPayData.UISettings.color,
              fontSize: PSHiPayData.UISettings.fontSize,
              fontWeight: PSHiPayData.UISettings.fontWeight,
              placeholderColor: PSHiPayData.UISettings.placeholderColor,
              iconColor: PSHiPayData.UISettings.iconColor,
              caretColor: PSHiPayData.UISettings.caretColor
            },
            components: {
              checkbox: {
                mainColor: PSHiPayData.UISettings.oneClickHighlightColor
              },
            },
            invalid: {
              color: '#D50000',
              caretColor: '#D50000'
            }
          }
        };
        if (PSHiPayData.cardSpecifics.tokensDetails) {
          hipayPaymentsConfig['card'].one_click.cards = PSHiPayData.cardSpecifics.tokensDetails;
        }

        hipayPaymentsInstances['card'] = hipayPayments.create('card', hipayPaymentsConfig['card']);
      }

      // Function to attach Apple Pay event handlers
      function attachApplePayEvents() {
        console.debug('Attach events for ApplePay');

        hipayPaymentsInstances['applepay'].on('paymentAuthorized', (applepayData) => {
          console.debug('ApplePay payment authorized');
          document.querySelector('.js-hipay-payments-hosted-fields-overlay-applepay').style.display = 'block';

          const formData = new FormData();
          formData.append('hipayData', JSON.stringify(applepayData));

          fetch(PSHiPayData.paymentControllerUrl, {
            method: 'POST',
            body: formData,
          })
            .then(response => {
              if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
              }
              return response.json();
            })
            .then(data => {
              if (data.success) {
                hipayPaymentsInstances['applepay'].completePaymentWithSuccess();
                window.top.location.href = data.redirectUrl;
              } else {
                hipayPaymentsInstances['applepay'].completePaymentWithFailure();
                document.querySelector('.js-hipay-payments-hosted-fields-overlay-paypal').style.display = 'none';
                document.getElementById('js-hipay-payments-paypal-error-message').innerHTML = data.message;
                document.getElementById('js-hipay-payments-paypal-error-message').style.display = 'block';
              }
            })
            .catch(error => {
              hipayPaymentsInstances['applepay'].completePaymentWithFailure();
              console.error('Fetch request failed:', error);
              document.querySelector('.js-hipay-payments-hosted-fields-overlay-paypal').style.display = 'none';
              document.getElementById('js-hipay-payments-paypal-error-message').innerHTML = 'Request failed: ' + error.message;
              document.getElementById('js-hipay-payments-paypal-error-message').style.display = 'block';
            });
        });

        hipayPaymentsInstances['applepay'].on('cancel', () => {
          console.debug('ApplePay payment cancelled');
          hipayPaymentsInstances['applepay'].completePaymentWithFailure();
          hipayPaymentsInstances['applepay'].destroy();
          window.location.reload();
        });

        hipayPaymentsInstances['applepay'].on('paymentUnauthorized', (error) => {
          console.debug('ApplePay payment unauthorized');
          console.debug(error);
          hipayPaymentsInstances['applepay'].completePaymentWithFailure();
        });
      }

      function attachPayPalEvents() {
        hipayPaymentsInstances['paypal'].on('paymentAuthorized', (paypalData) => {
          document.querySelector('.js-hipay-payments-hosted-fields-overlay-paypal').style.display = 'block';
          hipayPaymentsInstances['paypal'].destroy();

          const formData = new FormData();
          formData.append('hipayData', JSON.stringify(paypalData));

          fetch(PSHiPayData.paymentControllerUrl, {
            method: 'POST',
            body: formData,
          })
            .then(response => {
              if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
              }
              return response.json();
            })
            .then(data => {
              if (data.success) {
                window.top.location.href = data.redirectUrl;
              } else {
                document.querySelector('.js-hipay-payments-hosted-fields-overlay-paypal').style.display = 'none';
                document.getElementById('js-hipay-payments-paypal-error-message').innerHTML = data.message;
                document.getElementById('js-hipay-payments-paypal-error-message').style.display = 'block';
              }
            })
            .catch(error => {
              console.error('Fetch request failed:', error);
              document.querySelector('.js-hipay-payments-hosted-fields-overlay-paypal').style.display = 'none';
              document.getElementById('js-hipay-payments-paypal-error-message').innerHTML = 'Request failed: ' + error.message;
              document.getElementById('js-hipay-payments-paypal-error-message').style.display = 'block';
            });
        });

        hipayPaymentsInstances['paypal'].on('paymentUnauthorized', (error) => {
          hipayPaymentsInstances['paypal'].destroy();
          window.location.reload();
        });

        hipayPaymentsInstances['paypal'].on('cancel', () => {
          hipayPaymentsInstances['paypal'].destroy();
          window.location.reload();
        });
      }

      const promiseErrorHandlerPayPal = (event) => {
        if (event.reason && event.reason.message && event.reason.message.includes('request.customerShippingInformation')) {
          const paypalAddressMessage = document.querySelector('.js-hipay-payments-paypal-address-message');

          if (paypalAddressMessage) {
            const tcMessage = paypalAddressMessage.previousElementSibling;
            const fieldMatch = event.reason.message.match(/\.([^.]+)$/);
            const fieldInError = fieldMatch ? fieldMatch[1] : null;

            paypalAddressMessage.querySelectorAll('span').forEach(span => span.style.display = 'none');
            const specificErrorSpan = fieldInError ? paypalAddressMessage.querySelector(`.error-${fieldInError}`) : null;

            if (specificErrorSpan) {
              paypalAddressMessage.querySelector('[data-error-type="field"]').style.display = 'inline';
              specificErrorSpan.style.display = 'inline';
            } else {
              paypalAddressMessage.querySelector('[data-error-type="generic"]').style.display = 'inline';
            }

            paypalAddressMessage.style.display = 'block';
            if (tcMessage && tcMessage.classList.contains('js-hipay-payments-tc-message')) {
              tcMessage.style.display = 'none';
            }
          }

          event.preventDefault();
        }
      };

      window.addEventListener('unhandledrejection', promiseErrorHandlerPayPal);

      PSHiPayData.apmCodes.forEach((code) => {
        hipayPaymentsConfig[code] = {
          selector: 'js-hipay-payments-hosted-fields-form-'+code,
          template: 'auto',
          request: {
            amount: PSHiPayData.cartDetails.total,
            currency: PSHiPayData.cartDetails.currencyCode,
          },
          fields: {
            cardHolder: {
              defaultFirstname: prestashop.customer.firstname,
              defaultLastname: prestashop.customer.lastname
            }
          },
          styles: {
            base: {
              color: PSHiPayData.UISettings.color,
              fontSize: PSHiPayData.UISettings.fontSize,
              fontWeight: PSHiPayData.UISettings.fontWeight,
              placeholderColor: PSHiPayData.UISettings.placeholderColor,
              iconColor: PSHiPayData.UISettings.iconColor,
              caretColor: PSHiPayData.UISettings.caretColor
            },
            components: {
              checkbox: {
                mainColor: PSHiPayData.UISettings.oneClickHighlightColor
              },
            },
            invalid: {
              color: '#D50000',
              caretColor: '#D50000'
            }
          }
        };
        if ('paypal' === code) {
          hipayPaymentsConfig[code].paypalButtonStyle = {
            shape: 'rect',
            color: 'blue'
          };
          hipayPaymentsConfig[code].request.customerShippingInformation = {
            zipCode: PSHiPayData.cartDetails.shipping.zipcode,
            city: PSHiPayData.cartDetails.shipping.city,
            country: PSHiPayData.cartDetails.shipping.countryCode,
            streetaddress: PSHiPayData.cartDetails.shipping.address1,
            streetaddress2: PSHiPayData.cartDetails.shipping.address2,
            firstname: PSHiPayData.cartDetails.shipping.fistName,
            lastname: PSHiPayData.cartDetails.shipping.lastName
          };

          hipayPaymentsInstances[code] = hipayPayments.create(code, hipayPaymentsConfig[code]);
          if (hipayPaymentsInstances['paypal'] !== undefined && null !== hipayPaymentsInstances['paypal']) {
            attachPayPalEvents();
          }

          return;
        }
        if ('applepay' === code) {
          const applePayDeviceMessage = document.querySelector('.js-hipay-payments-applepay-device-message');

          if (!window.ApplePaySession) {
            if (applePayDeviceMessage) {
              applePayDeviceMessage.style.display = 'block';

              const tcMessage = applePayDeviceMessage.previousElementSibling;
              if (tcMessage && tcMessage.classList.contains('js-hipay-payments-tc-message')) {
                tcMessage.style.display = 'none';
              }
            }
            document.querySelector('#js-hipay-payments-hosted-fields-form-applepay').remove();

            return;
          }

          const applePayTotal = {
            label: PSHiPayData.translations.total,
            amount: `${PSHiPayData.cartDetails.total}`
          };

          const applePayRequest = {
            countryCode: PSHiPayData.cartDetails.countryCode,
            currencyCode: PSHiPayData.cartDetails.currencyCode,
            total: applePayTotal,
            supportedNetworks: ['visa', 'masterCard']
          };

          const applePayStyle = {
            type: 'plain',
            color: 'black'
          };

          hipayPaymentsConfig[code] = {
            displayName: PSHiPayData.cartDetails.shopName,
            request: applePayRequest,
            applePayStyle: applePayStyle,
            selector: 'js-hipay-payments-hosted-fields-form-'+code
          };

          const hipayPaymentsApplePayInstance = HiPay({
            username: PSHiPayData.applePaySpecifics.credentials.username,
            password: PSHiPayData.applePaySpecifics.credentials.password,
            environment: PSHiPayData.applePaySpecifics.credentials.env,
            lang: prestashop.language.iso_code,
          });

          if (PSHiPayData.applePaySpecifics.merchantIdentifier) {
            hipayPaymentsApplePayInstance.canMakePaymentsWithActiveCard(PSHiPayData.applePaySpecifics.merchantIdentifier).then((canMakePayments) => {
              if (canMakePayments) {
                hipayPaymentsInstances[code] = hipayPaymentsApplePayInstance.create('paymentRequestButton', hipayPaymentsConfig[code]);
                if (hipayPaymentsInstances['applepay'] !== undefined && null !== hipayPaymentsInstances['applepay']) {
                  attachApplePayEvents();
                }
              } else {
                if (applePayDeviceMessage) {
                  applePayDeviceMessage.style.display = 'block';

                  const tcMessage = applePayDeviceMessage.previousElementSibling;
                  if (tcMessage && tcMessage.classList.contains('js-hipay-payments-tc-message')) {
                    tcMessage.style.display = 'none';
                  }
                }
                document.querySelector('#js-hipay-payments-hosted-fields-form-applepay').remove();
              }
            });
          } else {
            if (window.ApplePaySession.canMakePayments()) {
              hipayPaymentsInstances[code] = hipayPaymentsApplePayInstance.create('paymentRequestButton', hipayPaymentsConfig[code]);
              if (hipayPaymentsInstances['applepay'] !== undefined && null !== hipayPaymentsInstances['applepay']) {
                attachApplePayEvents();
              }
            } else {
              if (applePayDeviceMessage) {
                applePayDeviceMessage.style.display = 'block';

                const tcMessage = applePayDeviceMessage.previousElementSibling;
                if (tcMessage && tcMessage.classList.contains('js-hipay-payments-tc-message')) {
                  tcMessage.style.display = 'none';
                }
              }
              document.querySelector('#js-hipay-payments-hosted-fields-form-applepay').remove();
            }
          }

          return;
        }
        if (['3xcb', '3xcb-no-fees', '4xcb', '4xcb-no-fees'].includes(code)) {
          const missingPhone = document.querySelector('.js-hipay-payments-oney-phone-message');

          if (missingPhone) {
            return;
          }
        }

        hipayPaymentsInstances[code] = hipayPayments.create(code, hipayPaymentsConfig[code]);
      });

      setTimeout(() => {
        window.removeEventListener('unhandledrejection', promiseErrorHandlerPayPal);
      }, 3000);

      Object.keys(hipayPaymentsInstances).forEach(( code) => {
        if (null === hipayPaymentsInstances[code]) {
          return;
        }
        hipayPaymentsInstances[code].on('change', (event) => {
          if (!event.valid) {
            document.querySelector('#payment-confirmation button').classList.add('disabled');
            document.querySelector('#payment-confirmation button').disabled = true;
          } else if (document.querySelector('input[name="conditions_to_approve[terms-and-conditions]"]').checked) {
            document.querySelector('#payment-confirmation button').classList.remove('disabled');
            document.querySelector('#payment-confirmation button').disabled = false;
            document.getElementById(`js-hipay-payments-${code}-error-message`).innerHTML = '';
            document.getElementById(`js-hipay-payments-${code}-error-message`).style.display = 'none';
          }
        });
      })

      Object.keys(hipayPaymentsInstances).forEach(( code) => {
        $('#js-hipay-payments-form-'+code).one('submit', (event) => {
          event.preventDefault();

          hipayPaymentsInstances[code].getPaymentData().then(
            (response) => {
              document.querySelector('.js-hipay-payments-hosted-fields-overlay-'+code).style.display = 'block';

              const formData = new FormData();
              formData.append('hipayData', JSON.stringify(response));
              document.getElementById('ioBB-'+code).value = response.device_fingerprint;

              fetch(PSHiPayData.paymentControllerUrl, {
                method: 'POST',
                body: formData,
              })
                .then(response => {
                  if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                  }
                  return response.json();
                })
                .then(data => {
                  if (data.success) {
                    window.top.location.href = data.redirectUrl;
                  } else {
                    document.querySelector('.js-hipay-payments-hosted-fields-overlay-'+code).style.display = 'none';
                    document.getElementById('js-hipay-payments-'+code+'-error-message').innerHTML = data.message;
                    document.getElementById('js-hipay-payments-'+code+'-error-message').style.display = 'block';
                  }
                })
                .catch(error => {
                  console.error('Fetch request failed:', error);
                  document.querySelector('.js-hipay-payments-hosted-fields-overlay-'+code).style.display = 'none';
                  document.getElementById('js-hipay-payments-'+code+'-error-message').innerHTML = 'Request failed: ' + error.message;
                  document.getElementById('js-hipay-payments-'+code+'-error-message').style.display = 'block';
                });
            },
            (errors) => {
              document.getElementById('js-hipay-payments-'+code+'-error-message').innerHTML = errors[0].error;
              document.getElementById('js-hipay-payments-'+code+'-error-message').style.display = 'block';
            }
          );
        });
      })
    };

    script.onerror = () => {
      console.error('Failed to load HiPay SDK');
    };

    // Append the script to the document head
    document.head.appendChild(script);


  } catch (error) {
    console.error('Error loading HiPay SDK:', error);
  }
})();
