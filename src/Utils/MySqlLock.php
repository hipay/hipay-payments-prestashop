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

namespace HiPay\PrestaShop\Utils;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class MySqlLock
 */
class MySqlLock
{
    /** @var \PDO */
    private $pdo;

    /** @var string|null */
    private $lockName;

    /** @var bool */
    private $acquired = false;

    /**
     * @throws \PDOException
     */
    public function __construct()
    {
        $this->pdo = new \PDO(
            sprintf('mysql:host=%s;dbname=%s', _DB_SERVER_, _DB_NAME_),
            _DB_USER_,
            _DB_PASSWD_,
            [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_TIMEOUT => 5,
            ]
        );
    }

    /**
     * @param string $lockName
     * @param int    $timeout
     * @return bool
     */
    public function acquire(string $lockName, int $timeout = 0): bool
    {
        $stmt = $this->pdo->prepare('SELECT GET_LOCK(:lockName, :timeout)');
        $stmt->execute([
            'lockName' => $lockName,
            'timeout' => $timeout,
        ]);

        $result = $stmt->fetchColumn();

        $this->acquired = ($result == 1);
        $this->lockName = $this->acquired ? $lockName : null;

        return $this->acquired;
    }

    /**
     * @return bool
     */
    public function release(): bool
    {
        if (!$this->acquired || $this->lockName === null) {
            return false;
        }

        $stmt = $this->pdo->prepare('SELECT RELEASE_LOCK(:lockName)');
        $stmt->execute(['lockName' => $this->lockName]);

        $result = $stmt->fetchColumn();

        $this->acquired = false;
        $this->lockName = null;

        return ($result == 1);
    }

    /**
     *
     */
    public function __destruct()
    {
        if ($this->acquired) {
            $this->release();
        }
    }
}
