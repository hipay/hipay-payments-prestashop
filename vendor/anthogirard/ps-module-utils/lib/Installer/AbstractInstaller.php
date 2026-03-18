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

/**
 * Class AbstractInstaller
 * @package AG\PSModuleUtils\Installer
 */
abstract class AbstractInstaller
{
    /** @var \Module $module */
    protected $module;

    /** @var \Monolog\Logger $logger */
    protected $logger;

    /** @var mixed[] $defaults */
    protected $defaults;

    /** @var TabManager $tabManager */
    protected $tabManager;

    /** @var OrderStateManager $orderStateManager */
    protected $orderStateManager;

    /** @var CarrierManager $carrierManager */
    protected $carrierManager;

    /**
     * AbstractInstaller constructor.
     * @param \Module               $module
     * @param AbstractLoggerFactory $loggerFactory
     */
    public function __construct(\Module $module, AbstractLoggerFactory $loggerFactory)
    {
        $this->module = $module;
        $this->logger = $loggerFactory->setChannel('Installer');
    }

    abstract public function checkTechnicalRequirements();
    abstract public function applyDefaultConfiguration();

    /**
     * @return void
     * @throws \Exception
     */
    public function runInstall()
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

    /**
     * @return void
     */
    public function runUninstall()
    {

    }

    /**
     * @return \Monolog\Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @return void
     */
    public function getYaml()
    {
        $parser = new Parser();
        $this->defaults = $parser->parse(file_get_contents($this->module->getLocalPath().'install/defaults.yml'));
        $this->logger->info('YAML file parsed');
    }

    /**
     * @return void
     */
    public function installDb()
    {
        $sqlLoader = new SqlLoader();
        $sqlLoader->setMetaData([
            'PREFIX_' => _DB_PREFIX_,
        ]);
        $sqlLoader->parse_file($this->module->getLocalPath().'install/install.sql');
        $this->logger->info('Database updated');
    }

    /**
     * @return void
     */
    public function registerHooks()
    {
        foreach ($this->defaults['hooks'] as $hook) {
            $this->logger->info(sprintf('Register hook %s', $hook));
            $this->module->registerHook($hook);
        }
    }

    /**
     * @return void
     */
    public function installTabs()
    {

    }

    /**
     * @return void
     */
    public function installOrderStates()
    {

    }

    /**
     * @return void
     */
    public function installCarriers()
    {

    }
}
