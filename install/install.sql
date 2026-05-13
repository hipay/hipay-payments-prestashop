/*
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

CREATE TABLE IF NOT EXISTS `PREFIX_hipaypayments_queued_notification`
(
    `id`                    INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `transaction_reference` VARCHAR(50)      NOT NULL COLLATE 'utf8mb4_general_ci',
    `status`                VARCHAR(50)      NOT NULL COLLATE 'utf8mb4_general_ci',
    `payload`               TEXT             NOT NULL COLLATE 'utf8mb4_general_ci',
    `received_at`           DATETIME         NOT NULL,
    `processed_at`          DATETIME         NULL     DEFAULT NULL,
    `attempts`              INT(11)          NOT NULL DEFAULT 0,
    `is_processed`          TINYINT(4)       NOT NULL DEFAULT 0,
    `is_failed`             TINYINT(4)       NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `transaction_reference_is_processed_is_failed` (`transaction_reference`, `is_processed`, `is_failed`) USING BTREE,
    INDEX `received_at` (`received_at`) USING BTREE
)
    COLLATE = 'utf8mb4_general_ci'
    ENGINE = InnoDB
;

CREATE TABLE IF NOT EXISTS `PREFIX_hipaypayments_order`
(
    `id_hipaypayments_order`      INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_order`                    INT(10) UNSIGNED NOT NULL,
    `id_cart`                     INT(10) UNSIGNED NOT NULL,
    `hipay_transaction_reference` VARCHAR(50)      NOT NULL COLLATE 'utf8mb4_general_ci',
    `hipay_order_id`              VARCHAR(50)      NOT NULL COLLATE 'utf8mb4_general_ci',
    `date_add`                    DATETIME         NOT NULL,
    PRIMARY KEY (`id_hipaypayments_order`) USING BTREE,
    INDEX `id_order` (`id_order`) USING BTREE,
    INDEX `hipay_transaction_reference` (`hipay_transaction_reference`) USING BTREE,
    INDEX `hipay_order_id` (`hipay_order_id`) USING BTREE
)
    COLLATE = 'utf8mb4_general_ci'
    ENGINE = InnoDB
;

CREATE TABLE IF NOT EXISTS `PREFIX_hipaypayments_customer_card`
(
    `id_hipaypayments_customer_card` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_customer`                    INT(10) UNSIGNED NOT NULL,
    `payment_product`                VARCHAR(50)      NOT NULL COLLATE 'utf8mb4_general_ci',
    `card_token`                     VARCHAR(128)     NOT NULL COLLATE 'utf8mb4_general_ci',
    `card_brand`                     VARCHAR(50)      NOT NULL COLLATE 'utf8mb4_general_ci',
    `card_pan`                       VARCHAR(20)      NOT NULL COLLATE 'utf8mb4_general_ci',
    `card_expiry_month`              VARCHAR(2)       NOT NULL COLLATE 'utf8mb4_general_ci',
    `card_expiry_year`               VARCHAR(4)       NOT NULL COLLATE 'utf8mb4_general_ci',
    `card_holder`                    VARCHAR(50)      NOT NULL COLLATE 'utf8mb4_general_ci',
    `date_add`                       DATETIME         NOT NULL,
    PRIMARY KEY (`id_hipaypayments_customer_card`) USING BTREE,
    INDEX `id_customer` (`id_customer`) USING BTREE
)
    COLLATE = 'utf8mb4_general_ci'
    ENGINE = InnoDB
;

CREATE TABLE IF NOT EXISTS `PREFIX_hipaypayments_moto_order`
(
    `id_hipaypayments_moto_order` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_order`                    INT(10) UNSIGNED NOT NULL,
    `id_cart`                     INT(10) UNSIGNED NOT NULL,
    `date_add`                    DATETIME         NOT NULL,
    PRIMARY KEY (`id_hipaypayments_moto_order`) USING BTREE,
    INDEX `id_order` (`id_order`) USING BTREE,
    INDEX `id_cart` (`id_cart`) USING BTREE
)
    COLLATE = 'utf8mb4_general_ci'
    ENGINE = InnoDB
;
