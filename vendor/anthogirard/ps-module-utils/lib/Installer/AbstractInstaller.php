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

use AG\PSModuleUtils\Logger\AbstractLoggerFactory;
use PrestaShopBundle\Install\SqlLoader;
use Symfony\Component\Yaml\Parser;

abstract class AbstractInstaller
{
    protected \Monolog\Logger $logger;
    protected array $defaults;
    protected TabManager $tabManager;
    protected OrderStateManager $orderStateManager;
    protected CarrierManager $carrierManager;

    public function __construct(protected \Module $module, AbstractLoggerFactory $loggerFactory)
    {
        $this->logger = $loggerFactory->withChannel('Installer');
    }

    abstract public function checkTechnicalRequirements(): void;
    abstract public function applyDefaultConfiguration(): void;

    /**
     * @throws \Exception
     */
    public function runInstall(): void
    {
        $context = ['module_version' => $this->module->version, 'prestashop_version' => _PS_VERSION_];
        $this->logger->info('Start install process', $context);
        $this->getYaml();
        $this->checkTechnicalRequirements();
        $this->installTabs();
        $this->installOrderStates();
        $this->installCarriers();
        $this->registerHooks();
        $this->installDb();
        $this->applyDefaultConfiguration();
        $this->logger->info('Install process finished with success');
    }

    public function runUninstall(): void
    {

    }

    public function getLogger(): \Monolog\Logger
    {
        return $this->logger;
    }

    public function getYaml(): void
    {
        $parser = new Parser();
        $this->defaults = $parser->parse(file_get_contents($this->module->getLocalPath().'install/defaults.yml'));
        $this->logger->info('YAML file parsed');
    }

    public function installDb(): void
    {
        $sqlLoader = new SqlLoader();
        $sqlLoader->setMetaData([
            'PREFIX_' => _DB_PREFIX_,
        ]);
        $sqlLoader->parse_file($this->module->getLocalPath().'install/install.sql');
        $this->logger->info('Database updated');
    }

    public function registerHooks(): void
    {
        foreach ($this->defaults['hooks'] as $hook) {
            $this->logger->info(sprintf('Register hook %s', $hook));
            $this->module->registerHook($hook);
        }
    }

    public function installTabs(): void
    {

    }

    public function installOrderStates(): void
    {

    }

    public function installCarriers(): void
    {

    }
}
