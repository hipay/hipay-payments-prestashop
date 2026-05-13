<?php
/*
 * MIT License
 *
 * Copyright (c) 2022 Anthony Girard
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 */

namespace AG\PSModuleUtils\Installer;

use Carrier;
use Configuration;
use Context;
use Language;
use AG\PSModuleUtils\Tools;
use Monolog\Logger;
use Validate;

/**
 * Class CarrierManager
 * @package AG\PSModuleUtils\Installer
 */
class CarrierManager
{
    /** @var Logger $logger */
    private $logger;

    /**
     * @param mixed[] $carriers
     * @param string  $moduleName
     * @return void
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function installCarriers($carriers, $moduleName)
    {
        $languages = Language::getLanguages(false);
        foreach ($carriers as $carrier) {
            $idCarrier = Configuration::getGlobalValue($carrier['configKey']);
            $oldCarrier = Carrier::getCarrierByReference((int) $idCarrier);
            if ($oldCarrier !== false &&
                Validate::isLoadedObject($oldCarrier) &&
                $oldCarrier->external_module_name == $moduleName
            ) {
                $this->logger->info('Carrier already exists');
                continue;
            }
            $this->logger->info(sprintf('Install carrier %s', $carrier['configKey']));
            $this->createCarrier($carrier, $languages, $moduleName);
        }
    }

    /**
     * @param mixed[] $moduleCarrier
     * @param mixed[] $languages
     * @param string  $moduleName
     * @return Carrier
     * @throws \PrestaShopException
     * @throws \Exception
     */
    public function createCarrier($moduleCarrier, $languages, $moduleName)
    {
        $carrier = new Carrier();
        $carrier->hydrate($moduleCarrier);
        foreach ($languages as $language) {
            if (isset($moduleCarrier['delays'][$language['iso_code']])) {
                $carrier->delay[(int) $language['id_lang']] = $moduleCarrier['delays'][$language['iso_code']];
            } else {
                $carrier->delay[(int) $language['id_lang']] = $moduleCarrier['delays']['en'];
            }
        }

        if (!$carrier->save()) {
            throw new \Exception('Cannot create carrier');
        }
        if (\Tools::version_compare(_PS_VERSION_, '1.7', '>=')) {
            $logoPath = _PS_MODULE_DIR_.$moduleName.'/views/img/carrier_icon_17.png';
        } else {
            $logoPath = _PS_MODULE_DIR_.$moduleName.'/views/img/carrier_icon.png';
        }
        Tools::copy($logoPath, _PS_SHIP_IMG_DIR_.(int) $carrier->id.'.jpg');
        Tools::copy($logoPath, _PS_TMP_IMG_DIR_.'carrier_mini_'.(int) $carrier->id.'_'.Context::getContext()->language->id.'.png');
        Configuration::updateGlobalValue($moduleCarrier['configKey'], $carrier->id);

        return $carrier;
    }

    /**
     * @param Logger $logger
     * @return void
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }
}
