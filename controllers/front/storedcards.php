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

/**
 * Class HiPayPaymentsStoredCardsModuleFrontController
 */
class HiPayPaymentsStoredCardsModuleFrontController extends ModuleFrontController
{
    /** @var HiPayPayments */
    public $module;

    /** @var \Monolog\Logger */
    public $logger;

    /** @var bool */
    public $needRedirect = false;

    public function __construct()
    {
        parent::__construct();
        /** @var \HiPay\PrestaShop\Logger\LoggerFactory $loggerFactory */
        $loggerFactory = $this->module->getService('hp.logger.factory');
        $this->logger = $loggerFactory->withChannel('StoredCards');
    }

    /**
     * @return mixed[]
     */
    public function getTemplateVarPage(): array
    {
        $page = parent::getTemplateVarPage();
        $page['body_classes']['page-customer-account'] = true;
        $page['meta']['robots'] = 'noindex';

        return $page;
    }

    /**
     * @return mixed[]
     */
    public function getBreadcrumbLinks(): array
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = $this->addMyAccountToBreadcrumb();

        return $breadcrumb;
    }

    /**
     * @return void
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function initContent()
    {
        if ($this->needRedirect) {
            $this->redirectWithNotifications($this->context->link->getModuleLink((string) $this->module->name, 'storedcards', []));
        }
        parent::initContent();
        try {
            /** @var \HiPayPaymentsCustomerCard[] $storeCards */
            $storeCards = \HiPayPaymentsCustomerCard::getCardsByCustomerId($this->context->customer->id);
        } catch (Exception $e) {
            $storeCards = [];
        }
        $tokensDetails = array_map(function($obj) {
            return [
                'id' => $obj->id_hipaypayments_customer_card,
                'brand' => $obj->card_brand,
                'pan' => $obj->card_pan,
                'card_expiry_month' => $obj->card_expiry_month,
                'card_expiry_year' => $obj->card_expiry_year,
                'card_holder' => $obj->card_holder,
            ];
        }, $storeCards);

        $this->context->smarty->assign([
            'customerTokens' => $tokensDetails,
        ]);

        $this->setTemplate(sprintf('module:%s/views/templates/front/storedCards.tpl', $this->module->name));
    }

    /**
     * @return void
     */
    public function postProcess()
    {
        if (Tools::getValue('deleteCard')) {
            try {
                $this->deleteCard();
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
                $this->errors[] = $this->module->l('Could not delete stored card.', 'storedcards');

                return;
            }
        }

        parent::postProcess();
    }

    /**
     * @return void
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws Exception
     */
    private function deleteCard()
    {
        $cardId = (int) Tools::getValue('cardId');
        $tokenSent = Tools::getValue('token');
        $tokenCalculated = Tools::getToken(true, $this->context);
        if (!$tokenSent || $tokenSent != $tokenCalculated) {
            throw new Exception('Token does not match');
        }
        $storedCard = new HiPayPaymentsCustomerCard((int) $cardId);
        if (!Validate::isLoadedObject($storedCard) || (int) $storedCard->id_customer !== (int) $this->context->customer->id) {
            throw new Exception('Card data does not match customer data');
        }

        if (!$storedCard->delete()) {
            throw new Exception('Stored card could not be deleted.');
        } else {
            $this->needRedirect = true;
            $this->success[] = $this->module->l('Card deleted successfully.', 'storedcards');
        }
    }
}
