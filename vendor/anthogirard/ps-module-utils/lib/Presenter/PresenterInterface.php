<?php
/*
 * 2022 Evolutive Group
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0).
 * It is also available through the world-wide-web at this URL: https://opensource.org/licenses/AFL-3.0
 *
 * @author    Evolutive Group
 * @copyright 2022 Evolutive Group
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *
 */

namespace AG\PSModuleUtils\Presenter;

/**
 * Interface PresenterInterface
 * @package AG\PSModuleUtils\Presenter
 */
interface PresenterInterface
{
    /**
     * @param mixed $object
     * @return mixed
     */
    public function present($object);
}
