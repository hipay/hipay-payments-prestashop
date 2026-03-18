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
 * Class AdminHiPayPaymentsLogsController
 */
class AdminHiPayPaymentsLogsController extends ModuleAdminController
{
    /** @var HiPayPayments $module */
    public $module;

    /**
     * @return void
     */
    public function processDownloadLogFile()
    {
        /** @var \Monolog\Logger $logger */
        $logger = $this->module->getService('hp.logger');
        $handlers = $logger->getHandlers();
        foreach ($handlers as $handler) {
            if ($handler instanceof \Monolog\Handler\RotatingFileHandler) {
                $file = $handler->getUrl();
                if (realpath($file)) {
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename="'.basename($file).'"');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');
                    readfile($file);
                    exit;
                }
            }
        }
        //@formatter:off
        $this->errors[] = $this->module->l('Log file not found. Make sure logs are enabled', 'AdminHiPayLogsController');
        //@formatter:on
    }
}
