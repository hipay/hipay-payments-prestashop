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
 * Class OrderController
 */
class OrderController extends OrderControllerCore
{
    /**
     * @return CheckoutStepInterface
     *
     * @throws \RuntimeException if no current step is found
     */
    public function getCurrentStep()
    {
        foreach ($this->checkoutProcess->getSteps() as $step) {
            if ($step->isCurrent()) {
                return $step;
            }
        }

        throw new \RuntimeException('There should be at least one current step');
    }
}
