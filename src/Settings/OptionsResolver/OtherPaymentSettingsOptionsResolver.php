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

namespace HiPay\PrestaShop\Settings\OptionsResolver;

use AG\PSModuleUtils\Settings\OptionsResolver\AbstractSettingsResolver;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class OtherPaymentSettingsOptionsResolver
 */
class OtherPaymentSettingsOptionsResolver extends AbstractSettingsResolver
{
    /**
     * @param mixed[] $parameters
     * @return mixed[]
     */
    public function resolve($parameters): array
    {
        $resolvedArray = $this->resolver->resolve($parameters);
        if (!empty($resolvedArray['paymentMethods'])) {
            $paymentMethods = [];
            foreach ($resolvedArray['paymentMethods'] as $paymentMethod) {
                $paymentMethods[] = $this->resolver->resolve($paymentMethod);
            }
            $resolvedArray['paymentMethods'] = $paymentMethods;
        }

        return $resolvedArray;
    }

    /**
     * @param OptionsResolver $resolver
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefined([
                'paymentMethods',
                'code',
                'name',
                'enabled',
                'minAmount',
                'maxAmount',
                'currenciesCountriesManaged',
                'currencies',
                'countries',
                'minAmountForced',
                'maxAmountForced',
                'countriesForced',
                'currenciesForced',
                'currenciesCountriesManaged',
                'displayMode',
                'canRefund',
                'position',
                'expirationLimit',
                'merchantIdentifier',
            ])
            ->setNormalizer(
                'code',
                function (Options $options, $value) {
                    return trim($value);
                }
            )
            ->setNormalizer(
                'name',
                function (Options $options, $value) {
                    return trim($value);
                }
            )
            ->setNormalizer(
                'enabled',
                function (Options $options, $value) {
                    return (bool) $value;
                }
            )
            ->setNormalizer(
                'currenciesCountriesManaged',
                function (Options $options, $value) {
                    return (bool) $value;
                }
            )
            ->setNormalizer(
                'minAmountForced',
                function (Options $options, $value) {
                    return (bool) $value;
                }
            )
            ->setNormalizer(
                'maxAmountForced',
                function (Options $options, $value) {
                    return (bool) $value;
                }
            )
            ->setNormalizer(
                'countriesForced',
                function (Options $options, $value) {
                    return (bool) $value;
                }
            )
            ->setNormalizer(
                'currenciesForced',
                function (Options $options, $value) {
                    return (bool) $value;
                }
            )
            ->setNormalizer(
                'countries',
                function (Options $options, $value) {
                    return '[]' === $value ? [] : array_map('trim', $value);
                }
            )
            ->setNormalizer(
                'currencies',
                function (Options $options, $value) {
                    return '[]' === $value ? [] : array_map('trim', $value);
                }
            )
            ->setNormalizer(
                'minAmount',
                function (Options $options, $value) {
                    return (float) $value;
                }
            )
            ->setNormalizer(
                'maxAmount',
                function (Options $options, $value) {
                    return (float) $value;
                }
            )
            ->setNormalizer(
                'canRefund',
                function (Options $options, $value) {
                    return (bool) $value;
                }
            )
            ->setNormalizer(
                'displayMode',
                function (Options $options, $value) {
                    return trim($value);
                }
            )
            ->setNormalizer(
                'position',
                function (Options $options, $value) {
                    return (int) $value;
                }
            )
            ->setNormalizer(
                'expirationLimit',
                function (Options $options, $value) {
                    return (int) $value;
                }
            )
            ->setNormalizer(
                'merchantIdentifier',
                function (Options $options, $value) {
                    return trim($value);
                }
            );
    }
}
