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

namespace HiPay\PrestaShop\Settings\Validation;

use AG\PSModuleUtils\Settings\Validation\AbstractValidationData;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class CardPaymentSettingsValidation
 */
class CardPaymentSettingsValidation extends AbstractValidationData
{
    /**
     * @param mixed[] $array
     * @return mixed[]
     */
    public function getValidationData($array): array
    {
        $constraints = [
            'paymentMethods' => new Assert\All([
                'constraints' => [
                    new Assert\Type('array'),
                    new Assert\Collection([
                        'fields' => [
                            'minAmount' => [
                                new Assert\Type('numeric'),
                            ],
                            'maxAmount' => [
                                new Assert\Type('numeric'),
                            ],
                        ],
                        'allowMissingFields' => true,
                        'allowExtraFields' => true,
                    ]),
                    new Assert\Callback(function ($item, ExecutionContextInterface $context) {
                        if (!is_array($item)) {
                            return; // Don't crash if bad input
                        }

                        if (
                            array_key_exists('minAmount', $item) &&
                            array_key_exists('maxAmount', $item) &&
                            is_numeric($item['minAmount']) &&
                            is_numeric($item['maxAmount']) &&
                            $item['maxAmount'] <= $item['minAmount'] &&
                            $item['maxAmount']
                        ) {
                            $context->buildViolation(sprintf($this->module->l('The maximum amount must be greater than minimum amount for payment method %s', 'CardPaymentSettingsValidation'), $item['name']))
                                ->atPath('[minAmount]')
                                ->addViolation();
                        }
                    }),
                ]
            ]),
        ];

        $arrayToValidate = array_intersect_key($array, $constraints);
        $validationConstraints = array_intersect_key($constraints, $array);

        return ['array' => $arrayToValidate, 'constraints' => $validationConstraints];
    }
}
