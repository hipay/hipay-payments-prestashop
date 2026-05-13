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

namespace AG\PSModuleUtils\Module;

use AG\PSModuleUtils\Exception\ExceptionList;
use AG\PSModuleUtils\Installer\AbstractInstaller;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Trait TraitModuleExtended
 * @package AG\PSModuleUtils\Module
 */
trait TraitModuleExtended
{
    /** @var \PrestaShop\ModuleLibServiceContainer\DependencyInjection\ServiceContainer $serviceContainer */
    protected $serviceContainer;

    /**
     * @param AbstractInstaller $installer
     * @return bool
     */
    public function installModule(AbstractInstaller $installer)
    {
        try {
            if (false === parent::install()) {
                $installer->getLogger()->error('parent::install() returns false');

                return false;
            }
            $installer->runInstall();
        } catch (ExceptionList $list) {
            $exceptions = $list->getExceptions();
            foreach ($exceptions as $e) {
                $installer->getLogger()->error(sprintf(
                    'Install Process: %s - File: %s - Line: %s - Trace: %s',
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine(),
                    $e->getTraceAsString()
                ));

                return false;
            }
        } catch (\Exception $e) {
            $installer->getLogger()->error(sprintf('Install Process: %s - File: %s - Line: %s - Trace: %s',
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
                $e->getTraceAsString()
            ));

            return false;
        }

        return true;
    }

    /**
     * @param AbstractInstaller $installer
     * @return bool
     */
    public function uninstallModule(AbstractInstaller $installer)
    {
        try {
            $installer->runUninstall();
        } catch (\Exception $e) {
            $installer->getLogger()->error(sprintf(
                'Uninstall Process: %s - File: %s - Line: %s - Trace: %s',
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
                $e->getTraceAsString()
            ));

            return false;
        }

        return true;
    }

    /**
     * @param string $controllerName
     * @return void
     */
    public function displayConfigurationPage($controllerName)
    {
        \Tools::redirectAdmin(\Context::getContext()->link->getAdminLink($controllerName));
    }

    /**
     * @param string|null $env
     * @return void
     * @throws \PrestaShopException
     */
    public function removeSymfonyCache($env = null)
    {
        if (null === $env) {
            $env = _PS_ENV_;
        }

        $dir = _PS_ROOT_DIR_ . '/var/cache/' . $env .'/';

        register_shutdown_function(function () use ($dir) {
            $fs = new Filesystem();
            $fs->remove($dir);
            \Hook::exec('actionClearSf2Cache');
        });
    }

    /**
     * @param string $serviceName
     * @return object|null
     */
    public function getService($serviceName)
    {
        if ($this->serviceContainer === null) {
            $this->serviceContainer = new \PrestaShop\ModuleLibServiceContainer\DependencyInjection\ServiceContainer(
                $this->name . str_replace('.', '', $this->version),
                $this->getLocalPath()
            );
        }

        return $this->serviceContainer->getService($serviceName);
    }
}
