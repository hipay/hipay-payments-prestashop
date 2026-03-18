<?php
/**
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

namespace HiPay\PrestaShop\Presenter;

use HiPay\PrestaShop\Settings\Entity\AbstractAdvancedPaymentMethod;
use HiPay\PrestaShop\Settings\Entity\AccountSettings;
use HiPay\PrestaShop\Settings\Entity\CardPaymentSettings;
use HiPay\PrestaShop\Settings\Settings;
use PrestaShop\PrestaShop\Adapter\Presenter\PresenterInterface;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class PaymentOptionsPresenter
 */
class PaymentOptionsPresenter implements PresenterInterface
{
    /** @var \HiPayPayments */
    private $module;

    /** @var Settings */
    private $settings;

    /** @var \Context */
    private $context;

    /**
     * PaymentOptionsPresenter Constructor.
     *
     * @param \HiPayPayments $module
     * @param Settings       $settings
     * @param \Context       $context
     */
    public function __construct(
        \HiPayPayments $module,
        Settings       $settings,
        \Context       $context
    ) {
        $this->module = $module;
        $this->settings = $settings;
        $this->context = $context;
    }

    /**
     * @param mixed $object
     * @return PaymentOption[]
     * @throws \SmartyException
     */
    public function present($object = null): array
    {
        $environmentText = false;
        $environmentBlockHTML = '';
        if ($this->settings->accountSettings->useDemoMode) {
            $environmentText = $this->module->l('demo', 'PaymentOptionsPresenter');
        } elseif (AccountSettings::MODE_TEST === $this->settings->accountSettings->environment) {
            $environmentText = $this->module->l('test', 'PaymentOptionsPresenter');
            $this->context->smarty->assign([
                'testingCardsUrl' => 'https://support.hipay.com/hc/fr/articles/213882649-Comment-tester-les-m%C3%A9thodes-de-paiement',
            ]);
        }
        if (false !== $environmentText) {
            $this->context->smarty->assign([
                'environmentText' => sprintf($this->module->l('You are using the %s environment.', 'PaymentOptionsPresenter'), $environmentText),
            ]);
            $environmentBlockHTML = $this->context->smarty->fetch('module:hipaypayments/views/templates/front/environmentInfo.tpl');
        }
        $paymentOptions = [];
        $cardPaymentCodes = $this->settings->cardPaymentSettings->getCardPaymentsCodes($this->context->cart);
        $paymentDisplayMode = CardPaymentSettings::DISPLAY_MODE_HOSTED_FIELDS === $this->settings->cardPaymentSettings->displayMode ? CardPaymentSettings::DISPLAY_MODE_HOSTED_FIELDS : (CardPaymentSettings::HOSTED_PAGE_TYPE_REDIRECT === $this->settings->cardPaymentSettings->hostedPageType ? CardPaymentSettings::HOSTED_PAGE_TYPE_REDIRECT : CardPaymentSettings::HOSTED_PAGE_TYPE_IFRAME);
        $availableAPM = $this->settings->otherPMSettings->getAPMDetails($this->context->cart);
        if ($cardPaymentCodes) {
            $redirectParams = ['paymentMethodCodes' => implode(',', $cardPaymentCodes)];
            $paymentOption = (new \PrestaShop\PrestaShop\Core\Payment\PaymentOption())
                ->setCallToActionText($this->module->l('Pay with credit or debit card', 'PaymentOptionsPresenter'))
                ->setLogo($this->module->getPathUri().'views/img/logos/CB_VISA_MC.svg')
                ->setAdditionalInformation($environmentBlockHTML);

            switch ($paymentDisplayMode) {
                case CardPaymentSettings::DISPLAY_MODE_HOSTED_FIELDS:
                    $this->context->smarty->assign([
                        'hiPayHFData' => [
                            'formID' => 'card',
                            'formAction' => $this->context->link->getModuleLink((string)$this->module->name, 'payment', ['token' => \Tools::getToken(), 'action' => 'sendHFPayment']),
                        ],
                    ]);
                    $paymentOption
                        ->setModuleName('hipay-payments-hf')
                        ->setForm($this->context->smarty->fetch('module:hipaypayments/views/templates/front/hostedFields.tpl'));
                    break;
                case CardPaymentSettings::HOSTED_PAGE_TYPE_REDIRECT:
                    $redirectParams['action'] = 'redirectToCardPayment';
                    $paymentOption
                        ->setAction($this->context->link->getModuleLink((string) $this->module->name, 'redirect', $redirectParams));
                    break;
                case CardPaymentSettings::HOSTED_PAGE_TYPE_IFRAME:
                    $paymentOption
                        ->setBinary(true)
                        ->setModuleName('hipay-payments-iframe');
                    break;
                default:
                    return [];
            }
            $paymentOptions[] = $paymentOption;
        }
        if ($availableAPM) {
            foreach ($availableAPM as $availableProduct) {
                $extraMessage = '';
                $tcMessage = '';
                switch ($availableProduct->displayMode) {
                    case AbstractAdvancedPaymentMethod::APM_DISPLAY_BINARY:
                        if ('applepay' === $availableProduct->code) {
                            $extraMessage = $this->context->smarty->fetch('module:hipaypayments/views/templates/front/applePayDeviceMessage.tpl');
                            $tcMessage = $this->context->smarty->fetch('module:hipaypayments/views/templates/front/tcMessage.tpl');
                        }
                        if ('paypal' === $availableProduct->code) {
                            $extraMessage = $this->context->smarty->fetch('module:hipaypayments/views/templates/front/paypalAddressMessage.tpl');
                            $tcMessage = $this->context->smarty->fetch('module:hipaypayments/views/templates/front/tcMessage.tpl');
                        }
                        $paymentOption = (new \PrestaShop\PrestaShop\Core\Payment\PaymentOption())
                            ->setCallToActionText(sprintf($this->module->l('Pay with %s', 'PaymentOptionsPresenter'), $availableProduct->name))
                            ->setAdditionalInformation($environmentBlockHTML.$tcMessage.$extraMessage)
                            ->setBinary(true)
                            ->setLogo(sprintf('%sviews/img/logos/%s.svg', $this->module->getPathUri(), $availableProduct->code))
                            ->setModuleName(sprintf('hipay-payments-apm-%s', $availableProduct->code));

                        break;
                    case AbstractAdvancedPaymentMethod::APM_DISPLAY_HOSTED_FIELDS:
                        if (in_array($availableProduct->code, ['3xcb', '4xcb', '3xcb-no-fees', '4xcb-no-fees'])) {
                            $shippingAddress = new \Address($this->context->cart->id_address_delivery);
                            if (!$shippingAddress->phone && !$shippingAddress->phone_mobile) {
                                $extraMessage = $this->context->smarty->fetch('module:hipaypayments/views/templates/front/oneyPhoneMessage.tpl');

                                // We display the payment option as binary to remove the "Pay" button in case of invalid address
                                $paymentOption = (new \PrestaShop\PrestaShop\Core\Payment\PaymentOption())
                                    ->setCallToActionText(sprintf($this->module->l('Pay with %s', 'PaymentOptionsPresenter'), $availableProduct->name))
                                    ->setAdditionalInformation($environmentBlockHTML.$extraMessage)
                                    ->setBinary(true)
                                    ->setLogo(sprintf('%sviews/img/logos/%s.svg', $this->module->getPathUri(), $availableProduct->code))
                                    ->setModuleName(sprintf('hipay-payments-apm-%s', $availableProduct->code));

                                break;
                            }
                        }

                        $paymentOption = (new \PrestaShop\PrestaShop\Core\Payment\PaymentOption())
                            ->setCallToActionText(sprintf($this->module->l('Pay with %s', 'PaymentOptionsPresenter'), $availableProduct->name))
                            ->setLogo(sprintf('%sviews/img/logos/%s.svg', $this->module->getPathUri(), $availableProduct->code))
                            ->setAdditionalInformation($environmentBlockHTML.$extraMessage);
                        $this->context->smarty->assign([
                            'hiPayHFData' => [
                                'formID' => $availableProduct->code,
                                'formAction' => $this->context->link->getModuleLink((string)$this->module->name, 'payment', ['token' => \Tools::getToken(), 'action' => 'sendHFPayment']),
                            ],
                        ]);
                        $paymentOption
                            ->setModuleName('hipay-payments-hf')
                            ->setForm($this->context->smarty->fetch('module:hipaypayments/views/templates/front/hostedFields.tpl'));

                        break;
                    case AbstractAdvancedPaymentMethod::APM_DISPLAY_REDIRECT:
                    default:
                        $redirectParams = [
                            'action' => 'redirectToAPM',
                            'paymentMethodCode' => $availableProduct->code,
                        ];
                        $paymentOption = (new \PrestaShop\PrestaShop\Core\Payment\PaymentOption())
                            ->setCallToActionText(sprintf($this->module->l('Pay with %s', 'PaymentOptionsPresenter'), $availableProduct->name))
                            ->setAdditionalInformation($environmentBlockHTML)
                            ->setLogo(sprintf('%sviews/img/logos/%s.svg', $this->module->getPathUri(), $availableProduct->code))
                            ->setAction($this->context->link->getModuleLink((string) $this->module->name, 'redirect', $redirectParams));

                        break;
                }
                $paymentOptions[] = $paymentOption;
            }
        }

        return $paymentOptions;
    }
}
