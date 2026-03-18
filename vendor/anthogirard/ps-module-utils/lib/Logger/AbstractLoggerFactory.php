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

namespace AG\PSModuleUtils\Logger;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use AG\PSModuleUtils\Tools;

/**
 * Class AbstractLoggerFactory
 * @package AG\PSModuleUtils\Logger
 */
abstract class AbstractLoggerFactory
{
    /** @var Logger $logger */
    private $logger;

    abstract public function getLoggerLevel();
    abstract public function getLogFilePath();

    /**
     *
     */
    public function __construct()
    {
        $this->logger = new Logger('module');
        $level = $this->getLoggerLevel();
        $fileHandler = new RotatingFileHandler(
            sprintf('%s%s.log', $this->getLogFilePath(), Tools::hash(_PS_MODULE_DIR_)),
            3,
            $level
        );
        $fileHandler->setFilenameFormat('{date}_{filename}', 'Ym');
        $this->logger->pushHandler($fileHandler)
                     ->pushProcessor(new UidProcessor(7));
    }

    /**
     * @deprecated Use withChannel() instead.
     * @param string $channel
     * @return Logger
     */
    public function setChannel($channel)
    {
        return $this->logger->withName($channel);
    }

    /**
     * @param string $channel
     * @return Logger
     */
    public function withChannel($channel)
    {
        return $this->logger->withName($channel);
    }

    /**
     * @return Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }
}
