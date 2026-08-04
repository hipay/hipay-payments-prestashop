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

document.addEventListener('DOMContentLoaded', function () {
  try {
    var radio = document.querySelector('input[data-module-name="hipay-payments-hostedpage"]');
    if (!radio || !radio.id) return;

    var additionalInfo = document.getElementById(radio.id + '-additional-information');
    if (!additionalInfo) return;

    var dataNode = additionalInfo.querySelector('.js-hipay-hostedpage-logos');
    if (!dataNode) return;

    var logos = JSON.parse(dataNode.getAttribute('data-logos') || '[]');
    if (!logos.length) return;

    var label = document.querySelector('label[for="' + radio.id + '"]');
    if (!label) return;

    var coreLogo = label.querySelector('img');

    var strip = document.createElement('span');
    strip.className = 'hipay-hp-logos';

    var visibleLogos = logos.slice(0, 3);
    var overflowLogos = logos.slice(3);

    visibleLogos.forEach(function (logo) {
      var img = document.createElement('img');
      img.src = logo.logo;
      img.alt = logo.name;
      strip.appendChild(img);
    });

    if (overflowLogos.length) {
      var badge = document.createElement('span');
      badge.className = 'hipay-hp-more';
      badge.textContent = '+' + overflowLogos.length;

      var tooltip = document.createElement('span');
      tooltip.className = 'hipay-hp-tooltip';

      overflowLogos.forEach(function (logo) {
        var row = document.createElement('span');
        row.className = 'hipay-hp-tooltip-row';

        var img = document.createElement('img');
        img.src = logo.logo;
        img.alt = logo.name;
        row.appendChild(img);

        var name = document.createElement('span');
        name.textContent = logo.name;
        row.appendChild(name);

        tooltip.appendChild(row);
      });

      badge.appendChild(tooltip);
      strip.appendChild(badge);
    }

    label.appendChild(strip);

    if (coreLogo) {
      coreLogo.style.display = 'none';
    }
  } catch (error) {
    console.error('Failed to initialize HiPay Hosted Page logo strip:', error);
  }
});
