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

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__.'/vendor/autoload.php';

/**
 * Class HiPayPayments
 */
class HiPayPayments extends PaymentModule
{
    use AG\PSModuleUtils\Module\TraitModuleExtended;

    /** @var string */
    public $theme;

    /**
     * HiPayPayments Constructor.
     */
    public function __construct()
    {
        $this->name = 'hipaypayments';
        $this->author = 'HiPay';
        $this->version = '3.2.0';
        $this->tab = 'payments_gateways';
        $this->ps_versions_compliancy = [
            'min' => '1.7.6',
            'max' => '1.8.0',
        ];
        parent::__construct();
        $this->bootstrap = true;
        //@formatter:off
        $this->displayName = $this->l('HiPay Payments');
        $this->description = $this->l('HiPay Payments module for PrestaShop');
        //@formatter:on
        $this->theme = Tools::version_compare(_PS_VERSION_, '1.7.7', '<') ? 'legacy' : 'new-theme';
    }

    /**
     * @return bool
     */
    public function install(): bool
    {
        $configurationCleared = $this->clearConfiguration();
        /** @var \HiPay\PrestaShop\Install\Installer $installer */
        $installer = $this->getService('hp.installer');

        return $configurationCleared && $this->installModule($installer);
    }

    /**
     * @return bool
     */
    public function clearConfiguration(): bool
    {
        Configuration::deleteByName('HIPAY_SETTINGS_ACCOUNT');
        Configuration::deleteByName('HIPAY_SETTINGS_MAIN');
        Configuration::deleteByName('HIPAY_SETTINGS_CARD_PAYMENT');
        Configuration::deleteByName('HIPAY_SETTINGS_OTHER_PM');
        Configuration::updateGlobalValue('HIPAY_SETTINGS_ACCOUNT', []);
        Configuration::updateGlobalValue('HIPAY_SETTINGS_MAIN', []);
        Configuration::updateGlobalValue('HIPAY_SETTINGS_CARD_PAYMENT', []);
        Configuration::updateGlobalValue('HIPAY_SETTINGS_OTHER_PM', []);

        return true;
    }

    /**
     * @param bool $force_all
     * @return bool
     * @throws PrestaShopException
     */
    public function disable($force_all = false): bool
    {
        if (parent::disable($force_all)) {
            return true;
        }

        return false;
    }

    /**
     * @param bool $force_all
     * @return bool
     * @throws PrestaShopException
     */
    public function enable($force_all = false): bool
    {
        if (parent::enable($force_all)) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     * @throws PrestaShopException
     */
    public function uninstall(): bool
    {
        /** @var \HiPay\PrestaShop\Install\Installer $installer */
        $installer = $this->getService('hp.installer');

        if (!parent::uninstall()) {
            return false;
        }

        if ($this->uninstallModule($installer) && $this->clearConfiguration()) {
            return true;
        }

        return false;
    }

    /**
     * @return void
     */
    public function getContent()
    {
        $this->displayConfigurationPage('AdminHiPayPaymentsConfiguration');
    }

    /**
     * @param mixed[] $params
     * @return void
     */
    public function hookActionAdminControllerSetMedia(array $params)
    {
        if ('AdminOrders' !== $this->context->controller->controller_name || !Tools::getValue('id_order')) {
            return;
        }
        $params['id_order'] = (int) Tools::getValue('id_order');
        $this->hookActionGetAdminOrderButtons($params);
        $this->context->controller->addCSS(_PS_MODULE_DIR_.$this->name.'/views/css/adminOrder.min.css');
        $this->context->controller->addJS(_PS_MODULE_DIR_.$this->name.'/views/js/admin-utils.js');
        $this->context->controller->addJS(_PS_MODULE_DIR_.$this->name.'/views/js/admin-order.js');
        if (\Tools::getValue('capture_success')) {
            $this->context->controller->confirmations[] = $this->l('Amount captured successfully.');
        }
        if (\Tools::getValue('refund_success')) {
            $this->context->controller->confirmations[] = $this->l('Amount refunded successfully.');
        }
    }

    /**
     * @param mixed[] $params
     * @return void
     */
    public function hookActionFrontControllerSetVariables(array $params)
    {
        $controller = $this->context->controller;
        if ($controller instanceof OrderController) {
            if ('checkout-payment-step' === $controller->getCurrentStep()->getIdentifier()) {
                /** @var HiPay\PrestaShop\Settings\Settings $settings */
                $settings = $this->getService('hp.settings');

                $cardPaymentCodes = [];
                if (\HiPay\PrestaShop\Settings\Entity\CardPaymentSettings::DISPLAY_MODE_HOSTED_FIELDS === $settings->cardPaymentSettings->displayMode) {
                    foreach ($settings->cardPaymentSettings->paymentMethods as $cardPaymentMethod) {
                        try {
                            if (true === $cardPaymentMethod->isEligibleWithCart($this->context->cart)) {
                                $cardPaymentCodes[] = $cardPaymentMethod->code;
                            }
                        } catch (\Exception $e) {
                            continue;
                        }
                    }
                }
                $apmList = $settings->otherPMSettings->getPaymentMethodsByDisplayMode([
                    HiPay\PrestaShop\Settings\Entity\AbstractAdvancedPaymentMethod::APM_DISPLAY_HOSTED_FIELDS,
                    HiPay\PrestaShop\Settings\Entity\AbstractAdvancedPaymentMethod::APM_DISPLAY_BINARY
                ]);
                $apmCodes = [];
                foreach ($apmList as $apm) {
                    try {
                        if (true === $apm->isEligibleWithCart($this->context->cart)) {
                            $apmCodes[] = $apm->code;
                        }
                    } catch (\Exception $e) {
                        continue;
                    }
                }

                if (!$cardPaymentCodes && !$apmCodes) {
                    return;
                }

                $controller->registerStylesheet('css-hipay-payments-iframe', sprintf('modules/%s/views/css/hipayPaymentsBinary.min.css', $this->name));
                $controller->registerJavascript('js-hipay-payments-hosted-fields', sprintf('modules/%s/views/js/ps-hosted-fields.js', $this->name));
                $controller->registerStylesheet('css-hipay-payments-hosted-fields', sprintf('modules/%s/views/css/hipayPaymentsHostedFields.min.css', $this->name));

                $tokensDetails = [];
                if (\HiPay\PrestaShop\Settings\Entity\CardPaymentSettings::DISPLAY_MODE_HOSTED_FIELDS === $settings->cardPaymentSettings->displayMode && $cardPaymentCodes) {
                    try {
                        /** @var \HiPayPaymentsCustomerCard[] $storeCards */
                        $storeCards = \HiPayPaymentsCustomerCard::getCardsByCustomerId($this->context->customer->id);
                    } catch (Exception $e) {
                        $storeCards = [];
                    }
                    if ($storeCards) {
                        $tokensDetails = array_map(function ($obj) {
                            return [
                                'token' => $obj->card_token,
                                'brand' => $obj->payment_product,
                                'pan' => $obj->card_pan,
                                'card_expiry_month' => $obj->card_expiry_month,
                                'card_expiry_year' => $obj->card_expiry_year,
                                'card_holder' => $obj->card_holder,
                            ];
                        }, $storeCards);
                    }
                }
                $cardSpecifics = [];
                if ($cardPaymentCodes) {
                    $cardSpecifics = [
                        'paymentMethodsCodes' => $cardPaymentCodes,
                        'oneClickEnabled' => $this->context->customer->isLogged() ? $settings->cardPaymentSettings->oneClickEnabled : false,
                        'tokensDetails' => $tokensDetails,
                        'selectors' => [],
                    ];
                }

                /** @var \HiPay\PrestaShop\Settings\Entity\APM\ApplePay|false $applePaySettings */
                $applePaySettings = $settings->otherPMSettings->findByCode('applepay');
                if (false !== $applePaySettings) {
                    $applePayMerchantIdentifier = $applePaySettings->merchantIdentifier;
                }
                $deliveryAddress = new Address((int) $this->context->cart->id_address_delivery);

                Media::addJsDef([
                    'PSHiPayData' => [
                        'credentials' => [
                            'username' => $settings->getPublicCredentials()->identifiers->username,
                            'password' => $settings->getPublicCredentials()->identifiers->password,
                            'env' => $settings->getPublicCredentials()->env,
                        ],
                        'UISettings' => [
                            'fontFamily' => $settings->cardPaymentSettings->UISettings->fontFamily,
                            'fontSize' => $settings->cardPaymentSettings->UISettings->fontSize,
                            'fontWeight' => $settings->cardPaymentSettings->UISettings->fontWeight,
                            'color' => $settings->cardPaymentSettings->UISettings->color,
                            'placeholderColor' => $settings->cardPaymentSettings->UISettings->placeholderColor,
                            'caretColor' => $settings->cardPaymentSettings->UISettings->caretColor,
                            'iconColor' => $settings->cardPaymentSettings->UISettings->iconColor,
                            'oneClickHighlightColor' => $settings->cardPaymentSettings->UISettings->oneClickHighlightColor,
                        ],
                        'cardSpecifics' => $cardSpecifics,
                        'applePaySpecifics' => [
                            'credentials' => [
                                'username' => $settings->getPublicCredentials(\HiPay\PrestaShop\Api\Credentials::CREDENTIALS_TYPE_APPLE_PAY)->identifiers->username,
                                'password' => $settings->getPublicCredentials(\HiPay\PrestaShop\Api\Credentials::CREDENTIALS_TYPE_APPLE_PAY)->identifiers->password,
                                'env' => $settings->getPublicCredentials(\HiPay\PrestaShop\Api\Credentials::CREDENTIALS_TYPE_APPLE_PAY)->env,
                            ],
                            'merchantIdentifier' => false !== $applePaySettings ? $applePayMerchantIdentifier : '',
                        ],
                        'apmCodes' => $apmCodes,
                        'paymentControllerUrl' => $this->context->link->getModuleLink((string) $this->name, 'payment', ['token' => \Tools::getToken(), 'action' => 'sendHFPayment']),
                        'cartDetails' => [
                            'total' => $this->context->cart->getOrderTotal(),
                            'currencyCode' => \AG\PSModuleUtils\Tools::getIsoCurrencyCodeById($this->context->cart->id_currency),
                            'countryCode' => Country::getIsoById((new Address($this->context->cart->id_address_delivery))->id_country),
                            'shopName' => $this->context->shop->name,
                            'shipping' => [
                                'firstName' => $deliveryAddress->firstname,
                                'lastName' => $deliveryAddress->lastname,
                                'address1' => $deliveryAddress->address1,
                                'address2' => $deliveryAddress->address2,
                                'city' => $deliveryAddress->city,
                                'zipcode' => $deliveryAddress->postcode,
                                'countryCode' => Country::getIsoById($deliveryAddress->id_country),
                            ],
                        ],
                        'translations' => [
                            'total' => $this->l('Total'),
                        ],
                    ],
                ]);
            }
        }
    }

    /**
     * @param mixed[] $params
     * @return void
     */
    public function hookActionGetAdminOrderButtons(array $params)
    {
        $idOrder = $params['id_order'];
        try {
            $hiPayOrder = HiPayPaymentsOrder::getHiPayOrderByPsOrderId($idOrder);
        } catch (Exception $e) {
            return;
        }

        Media::addJsDef([
            'hipayData' => [
                'idOrder' => $idOrder,
                'idHiPayOrder' => $hiPayOrder->id,
                'hipayTransactionReference' => $hiPayOrder->hipay_transaction_reference,
                'hipayAjaxController' => $this->context->link->getAdminLink('AdminHiPayPaymentsAjax'),
                'hipayAjaxTransactionController' => $this->context->link->getAdminLink('AdminHiPayPaymentsAjaxTransactions'),
            ],
        ]);
    }

    /**
     * @return string
     */
    public function hookDisplayCustomerAccount(): string
    {
        try {
            /** @var \HiPayPaymentsCustomerCard[] $storeCards */
            $storeCards = \HiPayPaymentsCustomerCard::getCardsByCustomerId($this->context->customer->id);
        } catch (Exception $e) {
            $storeCards = [];
        }
        if (!$storeCards) {
            return '';
        }

        return $this->display(__FILE__, 'views/templates/front/hookCustomerAccount.tpl');
    }

    /**
     * @return mixed[]
     */
    public function hookPaymentOptions(): array
    {
        /** @var \HiPay\PrestaShop\Presenter\PaymentOptionsPresenter $paymentOptionsPresenter */
        $paymentOptionsPresenter = $this->getService('hp.payment_options.presenter');
        try {
            $paymentOptions = $paymentOptionsPresenter->present();
        } catch (SmartyException $e) {
            $paymentOptions = [];
        }

        return $paymentOptions;
    }

    /**
     * @param \HiPay\PrestaShop\Settings\Settings $settings
     * @return string
     * @throws SmartyException
     */
    public function getCardPaymentByBinaries(\HiPay\PrestaShop\Settings\Settings $settings): string
    {
        if (\HiPay\PrestaShop\Settings\Entity\CardPaymentSettings::DISPLAY_MODE_HOSTED_FIELDS === $settings->cardPaymentSettings->displayMode ||
            \HiPay\PrestaShop\Settings\Entity\CardPaymentSettings::HOSTED_PAGE_TYPE_REDIRECT === $settings->cardPaymentSettings->hostedPageType
        ) {
            return '';
        }
        $cardPaymentCodes = $settings->cardPaymentSettings->getCardPaymentsCodes($this->context->cart);
        if ($cardPaymentCodes) {
            $redirectParams = ['paymentMethodCodes' => implode(',', $cardPaymentCodes)];
        }
        $redirectParams['action'] = 'redirectToCardPaymentIframe';
        $redirectParams['iframe'] = true;
        $this->context->smarty->assign([
            'hipayPaymentsIframeSrc' => $this->context->link->getModuleLink((string) $this->name, 'redirect', $redirectParams),
        ]);

        return (string) $this->context->smarty->fetch('module:hipaypayments/views/templates/front/hookDisplayPaymentByBinaries.tpl');
    }

    /**
     * @param \HiPay\PrestaShop\Settings\Settings $settings
     * @return string
     */
    public function getPayPalPaymentByBinaries(\HiPay\PrestaShop\Settings\Settings $settings): string
    {
        $paypal = $settings->otherPMSettings->findByCode('paypal');
        try {
            if (!$paypal || !$paypal->isEligibleWithCart($this->context->cart)) {
                return '';
            }

            return (string) $this->context->smarty->fetch('module:hipaypayments/views/templates/front/hookDisplayPaymentByBinaries_paypal.tpl');
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * @param \HiPay\PrestaShop\Settings\Settings $settings
     * @return string
     */
    public function getApplePayPaymentByBinaries(\HiPay\PrestaShop\Settings\Settings $settings): string
    {
        $applePay = $settings->otherPMSettings->findByCode('applepay');
        try {
            if (!$applePay || !$applePay->isEligibleWithCart($this->context->cart)) {
                return '';
            }

            return (string) $this->context->smarty->fetch('module:hipaypayments/views/templates/front/hookDisplayPaymentByBinaries_applepay.tpl');
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * @param mixed[] $params
     * @return string
     * @throws SmartyException
     */
    public function hookDisplayPaymentByBinaries(array $params): string
    {
        /** @var \HiPay\PrestaShop\Settings\Settings $settings */
        $settings = $this->getService('hp.settings');
        $html = $this->getCardPaymentByBinaries($settings);
        $html .= $this->getPayPalPaymentByBinaries($settings);
        $html .= $this->getApplePayPaymentByBinaries($settings);

        return $html;
    }

    /**
     * @param int    $idOrder
     * @param string $templateName
     * @return string
     */
    public function displayAdminOrder(int $idOrder, string $templateName): string
    {
        try {
            $hiPayOrder = HiPayPaymentsOrder::getHiPayOrderByPsOrderId($idOrder);
            $hiPayMotoOrder = HiPayPaymentsMotoOrder::getHiPayMotoOrderByPsOrderId($idOrder);
        } catch (Exception $e) {
            return '';
        }

        if (Validate::isLoadedObject($hiPayOrder)) {
            return $this->display(__FILE__, sprintf('views/templates/admin/%s/%s.tpl', $this->theme, $templateName));
        } elseif (Validate::isLoadedObject($hiPayMotoOrder)) {
            $cart = new Cart((int) $hiPayMotoOrder->id_cart);
            /** @var \HiPay\PrestaShop\Settings\SettingsLoader $settingsLoader */
            $settingsLoader = $this->getService('hp.settings.loader');
            $settings = $settingsLoader->withContext($cart->id_shop, $cart->id_shop_group, true);
            $cardPaymentCodes = $settings->cardPaymentSettings->getCardPaymentsCodes($cart);

            /** @var \HiPay\PrestaShop\Builder\PaymentRequestBuilderFactory $requestBuilderFactory */
            $requestBuilderFactory = $this->getService('hp.payment_request.builder');
            try {
                /** @var \HiPay\PrestaShop\Builder\CardRequestBuilder $builder */
                $builder = $requestBuilderFactory->create(
                    [
                        'payment_product' => implode(',', $cardPaymentCodes),
                        'moto' => true,
                        'orderId' => $idOrder,
                    ],
                    $cart,
                    true
                );
                /** @var \HiPay\Fullservice\Gateway\Request\Order\HostedPaymentPageRequest $hostedPaymentRequest */
                $hostedPaymentRequest = $builder->buildRequest();

                /** @var \HiPay\PrestaShop\Api\PrestaShopSDK $sdk */
                $sdk = $this->getService('hp.sdk.gateway');
                $transaction = $sdk
                    ->init($cart->id_shop, $cart->id_shop_group, \HiPay\PrestaShop\Api\Credentials::CREDENTIALS_TYPE_MOTO)
                    ->server()
                    ->requestHostedPaymentPage($hostedPaymentRequest);
                $forwardUrl = $transaction->getForwardUrl();
            } catch (Exception $e) {
                $this->context->smarty->assign([
                    'hiPayMotoError' => $e->getMessage(),
                ]);

                return $this->display(__FILE__, sprintf('views/templates/admin/%s/%s_moto.tpl', $this->theme, $templateName));
            }

            $this->context->smarty->assign([
                'hiPayPaymentLinkMoto' => $forwardUrl,
                'motoAccountConfigured' => $settings->accountSettings->motoCredentialsIsConfigured(),
            ]);

            return $this->display(__FILE__, sprintf('views/templates/admin/%s/%s_moto.tpl', $this->theme, $templateName));
        }

        return '';
    }

    /**
     * @param mixed[] $params
     * @return string
     */
    public function hookDisplayAdminOrderMainBottom(array $params): string
    {
        if (Tools::version_compare(_PS_VERSION_, '1.7.7', '<')) {
            return '';
        }

        return $this->displayAdminOrder((int) Tools::getValue('id_order'), 'hookDisplayAdminOrderMainBottom');
    }

    /**
     * @param mixed[] $params
     * @return string
     */
    public function hookDisplayAdminOrderLeft(array $params): string
    {
        if (Tools::version_compare(_PS_VERSION_, '1.7.7', '>=')) {
            return '';
        }

        return $this->displayAdminOrder((int) Tools::getValue('id_order'), 'hookDisplayAdminOrderLeft');
    }

    /**
     * @param mixed[] $params
     * @return false|string
     * @throws SmartyException
     */
    public function hookDisplayPaymentTop(array $params)
    {
        if (Tools::getValue('hipayError')) {
            return $this->context->smarty->fetch($this->getLocalPath().'/views/templates/front/hookDisplayPaymentTop.tpl');
        }

        return '';
    }

    /**
     * @param mixed[] $params
     * @return void
     */
    public function hookActionValidateOrder(array $params)
    {
        /** @var Order $order */
        $order = $params['order'];
        if (!defined('_PS_ADMIN_DIR_') || $order->module !== $this->name) {
            return;
        }

        /** @var \HiPay\PrestaShop\Logger\LoggerFactory $loggerFactory */
        $loggerFactory = $this->getService('hp.logger.factory');
        $logger = $loggerFactory->withChannel('MOTO');
        $hipayMotoOrder = new HiPayPaymentsMotoOrder();
        $hipayMotoOrder->id_order = (int) $order->id;
        $hipayMotoOrder->id_cart = (int) $order->id_cart;
        try {
            $logger->debug('Saving MOTO Order', ['orderId' => (int) $order->id, 'cartId' => (int) $order->id_cart]);
            $hipayMotoOrder->save();
            $logger->debug('MOTO order saved', ['orderId' => (int) $order->id, 'cartId' => (int) $order->id_cart]);
        } catch (PrestaShopException $e) {
            $logger->error($e->getMessage());
        }
    }
}
