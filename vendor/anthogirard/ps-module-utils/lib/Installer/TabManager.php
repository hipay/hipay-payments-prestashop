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

use Language;
use Monolog\Logger;
use Tab;

/**
 * Class TabManager
 * @package AG\PSModuleUtils\Installer
 */
class TabManager
{
    /** @var Logger $logger */
    private $logger;

    /**
     * @param mixed[] $tabs
     * @param string  $moduleName
     * @return void
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function installTabs($tabs, $moduleName)
    {
        foreach ($tabs as $tab) {
            $this->logger->info(sprintf('Install tab %s', $tab['className']));
            $this->createTab($tab, $moduleName);
        }
    }

    /**
     * @param mixed[] $moduleTab
     * @param string  $moduleName
     * @return void
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \Exception
     */
    public function createTab($moduleTab, $moduleName)
    {
        if (Tab::getIdFromClassName($moduleTab['className'])) {
            return;
        }
        $tab = new Tab();
        $tab->class_name = pSQL($moduleTab['className']);
        $tab->module = pSQL($moduleName);
        $tab->id_parent = (int) Tab::getIdFromClassName($moduleTab['parentClassName']);
        $tab->active = true;
        $tab->name = [];
        $names = $moduleTab['names'];
        foreach (Language::getLanguages() as $lang) {
            $isoCode = $lang['iso_code'];
            $tabName = isset($names[$isoCode]) ? $names[$isoCode] : $names['en'];
            $tab->name[$lang['id_lang']] = pSQL($tabName);
        }

        if (!$tab->add()) {
            throw new \Exception('Cannot add menu.');
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
