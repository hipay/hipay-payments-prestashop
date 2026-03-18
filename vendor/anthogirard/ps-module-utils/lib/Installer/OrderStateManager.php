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

use Configuration;
use Language;
use AG\PSModuleUtils\Tools;
use Monolog\Logger;
use OrderState;
use Validate;

/**
 * Class OrderStatusManager
 * @package AG\PSModuleUtils\Installer
 */
class OrderStateManager
{
    /** @var Logger $logger */
    private $logger;

    /**
     * @param mixed[] $orderStates
     * @param string  $moduleName
     * @return void
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function installOrderStates($orderStates, $moduleName)
    {
        $languages = Language::getLanguages(false);
        foreach ($orderStates as $orderState) {
            $this->createOrderState($orderState, $languages, $moduleName);
        }
    }

    /**
     * @param mixed[] $moduleOrderState
     * @param mixed[] $languages
     * @param string  $moduleName
     * @return void
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function createOrderState($moduleOrderState, $languages, $moduleName)
    {
        $orderState = new OrderState((int) Configuration::getGlobalValue($moduleOrderState['configKey']));
        if (!Validate::isLoadedObject($orderState) || $orderState->deleted) {
            $this->logger->info(sprintf('Install order status %s', $moduleOrderState['configKey']));
            $orderState->hydrate($moduleOrderState);
            $orderState->module_name = pSQL($moduleName);
            $names = $moduleOrderState['names'];
            foreach ($languages as $language) {
                $name = isset($names[$language['iso_code']]) ? $names[$language['iso_code']] : $names['en'];
                $orderState->name[(int) $language['id_lang']] = pSQL($name);
            }
            if ($orderState->save()) {
                if ($moduleOrderState['logo']) {
                    $source = realpath(_PS_MODULE_DIR_.$moduleName.'/views/img/icons/'.$moduleOrderState['logo']);
                    $destination = _PS_ROOT_DIR_.'/img/os/'.(int) $orderState->id.'.gif';
                    Tools::copy($source, $destination);
                }
                Configuration::updateGlobalValue($moduleOrderState['configKey'], (int) $orderState->id);
            }
        } else {
            $this->logger->info(sprintf('Order status %s already exists', $moduleOrderState['configKey']));
        }
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
