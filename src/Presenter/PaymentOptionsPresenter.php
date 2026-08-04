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
use HiPay\PrestaShop\Settings\Entity\MainSettings;
use HiPay\PrestaShop\Settings\Settings;
use HiPay\PrestaShop\Settings\SettingsLoader;
use libphonenumber\NumberParseException;
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

    /** @var SettingsLoader */
    private $settingsLoader;

    /** @var \Context */
    private $context;

    /**
     * PaymentOptionsPresenter Constructor.
     *
     * @param \HiPayPayments $module
     * @param SettingsLoader $settingsLoader
     * @param \Context       $context
     */
    public function __construct(
        \HiPayPayments $module,
        SettingsLoader $settingsLoader,
        \Context       $context
    ) {
        $this->module = $module;
        $this->settingsLoader = $settingsLoader;
        $this->context = $context;
    }

    /**
     * @param mixed $object
     * @return PaymentOption[]
     * @throws \SmartyException
     */
    public function present($object = null): array
    {
        $this->settings = $this->settingsLoader->withContext(
            (int) $this->context->cart->id_shop,
            (int) $this->context->cart->id_shop_group,
            true
        );

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
        $cardDisplayMode = $this->settings->cardPaymentSettings->displayMode;
        if (CardPaymentSettings::DISPLAY_MODE_HOSTED_PAGE === $cardDisplayMode && true !== $this->settings->mainSettings->hostedPageEnabled) {
            $cardDisplayMode = CardPaymentSettings::DISPLAY_MODE_HOSTED_FIELDS;
        }
        $paymentDisplayMode = CardPaymentSettings::DISPLAY_MODE_HOSTED_FIELDS === $cardDisplayMode ? CardPaymentSettings::DISPLAY_MODE_HOSTED_FIELDS : (CardPaymentSettings::HOSTED_PAGE_TYPE_REDIRECT === $this->settings->cardPaymentSettings->hostedPageType ? CardPaymentSettings::HOSTED_PAGE_TYPE_REDIRECT : CardPaymentSettings::HOSTED_PAGE_TYPE_IFRAME);
        $availableAPM = $this->settings->otherPMSettings->getAPMDetails($this->context->cart);

        $cardInHostedPage = $cardPaymentCodes && CardPaymentSettings::DISPLAY_MODE_HOSTED_FIELDS !== $paymentDisplayMode;

        $hostedPageAPMCodes = [];
        $hostedPageLogos = [];
        if (true === $this->settings->mainSettings->hostedPageEnabled) {
            $hostedPageAPM = $this->settings->otherPMSettings->getHostedPageAPMDetails($this->context->cart);
            $hostedPageAPMCodes = array_map(function ($availableProduct) {
                return $availableProduct->code;
            }, $hostedPageAPM);
            $hostedPageLogos = array_map(function ($availableProduct) {
                return [
                    'code' => $availableProduct->code,
                    'name' => $availableProduct->name,
                    'logo' => sprintf('%sviews/img/logos/%s.svg', $this->module->getPathUri(), $availableProduct->code),
                ];
            }, $hostedPageAPM);
            $availableAPM = array_filter($availableAPM, function ($availableProduct) {
                return AbstractAdvancedPaymentMethod::CHANNEL_HOSTED_PAGE !== $availableProduct->channel;
            });

            if ($cardInHostedPage) {
                $hostedPageAPMCodes = array_merge($cardPaymentCodes, $hostedPageAPMCodes);
                array_unshift($hostedPageLogos, [
                    'code' => 'CB_VISA_MC',
                    'name' => $this->module->l('Credit or debit card', 'PaymentOptionsPresenter'),
                    'logo' => sprintf('%sviews/img/logos/CB_VISA_MC.svg', $this->module->getPathUri()),
                ]);
            }
        }

        if ($cardPaymentCodes && !$cardInHostedPage) {
            $paymentOption = (new \PrestaShop\PrestaShop\Core\Payment\PaymentOption())
                ->setCallToActionText($this->module->l('Pay with credit or debit card', 'PaymentOptionsPresenter'))
                ->setLogo($this->module->getPathUri().'views/img/logos/CB_VISA_MC.svg')
                ->setAdditionalInformation($environmentBlockHTML)
                ->setModuleName('hipay-payments-hf');

            $this->context->smarty->assign([
                'hiPayHFData' => [
                    'formID' => 'card',
                    'formAction' => $this->context->link->getModuleLink((string)$this->module->name, 'payment', ['token' => \Tools::getToken(), 'action' => 'sendHFPayment']),
                ],
            ]);
            $paymentOption->setForm($this->context->smarty->fetch('module:hipaypayments/views/templates/front/hostedFields.tpl'));

            $paymentOptions[] = $paymentOption;
        }

        $hostedPageOption = null;
        if ($hostedPageAPMCodes) {
            $idLang = (int) $this->context->language->id;
            $hostedPageLabel = $this->settings->mainSettings->hostedPageLabel[$idLang]
                ?? MainSettings::DEFAULT_HOSTED_PAGE_LABEL;
            $this->context->smarty->assign([
                'hiPayHostedPageLogosJson' => htmlspecialchars(json_encode($hostedPageLogos), ENT_QUOTES),
            ]);
            $hostedPageLogosHTML = $this->context->smarty->fetch('module:hipaypayments/views/templates/front/hostedPageLogos.tpl');

            $hostedPageOption = (new \PrestaShop\PrestaShop\Core\Payment\PaymentOption())
                ->setCallToActionText($hostedPageLabel)
                ->setModuleName('hipay-payments-hostedpage')
                ->setLogo($hostedPageLogos[0]['logo'])
                ->setAdditionalInformation($environmentBlockHTML.$hostedPageLogosHTML);

            if (CardPaymentSettings::HOSTED_PAGE_TYPE_IFRAME === $this->settings->mainSettings->hostedPageType) {
                $hostedPageOption->setBinary(true);
            } else {
                $hostedPageOption->setAction($this->context->link->getModuleLink((string) $this->module->name, 'redirect', [
                    'action' => 'redirectToCardPayment',
                    'paymentMethodCodes' => implode(',', $hostedPageAPMCodes),
                    'forceHostedPage' => 1,
                ]));
            }
        }

        if ($hostedPageOption && MainSettings::POSITION_ABOVE === $this->settings->mainSettings->hostedPagePosition) {
            $paymentOptions[] = $hostedPageOption;
        }

        $apmOptions = [];
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
                            $addressIsoCountry = (string) \Country::getIsoById($shippingAddress->id_country);
                            $phoneNumberUtil = \libphonenumber\PhoneNumberUtil::getInstance();
                            $isPhoneValid = null;
                            $isMobilePhoneValid = null;

                            if ($shippingAddress->phone) {
                                try {
                                    $phoneNumberObject = $phoneNumberUtil->parse($shippingAddress->phone, $addressIsoCountry);
                                    $isPhoneValid = $phoneNumberUtil->isValidNumber($phoneNumberObject);
                                } catch (NumberParseException $e) {
                                    $isPhoneValid = false;
                                }
                            }

                            if ($shippingAddress->phone_mobile) {
                                try {
                                    $mobilePhoneNumberObject = $phoneNumberUtil->parse($shippingAddress->phone_mobile, $addressIsoCountry);
                                    $isMobilePhoneValid = $phoneNumberUtil->isValidNumber($mobilePhoneNumberObject);
                                } catch (NumberParseException $e) {
                                    $isMobilePhoneValid = false;
                                }
                            }

                            $hasValidPhone = $isPhoneValid === true || $isMobilePhoneValid === true;
                            if (!$hasValidPhone) {
                                $fixedPhoneExample = $phoneNumberUtil->getExampleNumberForType($addressIsoCountry, \libphonenumber\PhoneNumberType::FIXED_LINE);
                                $mobilePhoneExample = $phoneNumberUtil->getExampleNumberForType($addressIsoCountry, \libphonenumber\PhoneNumberType::MOBILE);
                                $this->context->smarty->assign([
                                    'oneyWarningMessage' => sprintf(
                                        $this->module->l('Please make sure you have a valid phone number (e.g. %s or %s) in your delivery address before placing your order.', 'PaymentOptionsPresenter'),
                                        $phoneNumberUtil->format($fixedPhoneExample, \libphonenumber\PhoneNumberFormat::E164),
                                        $phoneNumberUtil->format($mobilePhoneExample, \libphonenumber\PhoneNumberFormat::E164)
                                    ),
                                ]);
                                $extraMessage = $this->context->smarty->fetch('module:hipaypayments/views/templates/front/oneyWarningMessage.tpl');
                            }
                        }

                        if ('bancomatpay' === $availableProduct->code) {
                            $extraMessage .= $this->context->smarty->fetch('module:hipaypayments/views/templates/front/bancomatPayMessage.tpl');
                        }

                        if ('bizum' === $availableProduct->code) {
                            $extraMessage .= $this->context->smarty->fetch('module:hipaypayments/views/templates/front/bizumMessage.tpl');
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
                $apmOptions[] = $paymentOption;
            }
        }

        foreach ($apmOptions as $apmOption) {
            $paymentOptions[] = $apmOption;
        }

        if ($hostedPageOption && MainSettings::POSITION_BELOW === $this->settings->mainSettings->hostedPagePosition) {
            $paymentOptions[] = $hostedPageOption;
        }

        return $paymentOptions;
    }
}
