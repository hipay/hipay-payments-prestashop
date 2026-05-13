<?php
/*
 * 2022 Client Name
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0).
 * It is also available through the world-wide-web at this URL: https://opensource.org/licenses/AFL-3.0
 *
 * @author    PrestaShop partner
 * @copyright 2022 Client Name
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *
 */

namespace Tests\ModuleUtils\PrestaShop\Exception;

use AG\PSModuleUtils\Exception\ExceptionList;
use PHPUnit\Framework\TestCase;

/**
 * Class ExceptionListTest
 * @package Tests\ModuleUtils\PrestaShop\Exception
 */
class ExceptionListTest extends TestCase
{
    /**
     * @return void
     */
    public function testArrayOfMessages()
    {
        $message1 = 'This is the first message';
        $exception1 = new \Exception($message1);
        $message2 = 'This is the second message';
        $exception2 = new \Exception($message2);
        $exceptionList = new ExceptionList();
        $exceptionList->setExceptions([$exception1, $exception2]);
        try {
            throw $exceptionList;
        } catch (ExceptionList $exceptionList) {
            $messages = $exceptionList->getExceptionsMessages();
            $this->assertCount(2, $messages);
            $this->assertEquals($message1, $messages[0]);
            $this->assertEquals($message2, $messages[1]);
        }
    }
}
