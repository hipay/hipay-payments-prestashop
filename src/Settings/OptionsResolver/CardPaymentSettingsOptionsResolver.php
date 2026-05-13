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
 * Class CardPaymentSettingsOptionsResolver
 */
class CardPaymentSettingsOptionsResolver extends AbstractSettingsResolver
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
        if (!empty($resolvedArray['UISettings'])) {
            $resolvedArray['UISettings'] = $this->resolver->resolve($parameters['UISettings']);
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
                'displayMode',
                'hostedPageType',
                'threeDSMode',
                'cancelButtonDisplayed',
                'oneClickEnabled',
                'paymentMethods',
                'code',
                'name',
                'enabled',
                'minAmount',
                'maxAmount',
                'currenciesCountriesManaged',
                'currencies',
                'countries',
                'canRefund',
                'UISettings',
                'fontFamily',
                'fontSize',
                'fontWeight',
                'color',
                'placeholderColor',
                'caretColor',
                'iconColor',
                'oneClickHighlightColor',
            ])
            ->setNormalizer(
                'displayMode',
                function (Options $options, $value) {
                    return trim($value);
                }
            )
            ->setNormalizer(
                'hostedPageType',
                function (Options $options, $value) {
                    return trim($value);
                }
            )
            ->setNormalizer(
                'threeDSMode',
                function (Options $options, $value) {
                    return trim($value);
                }
            )
            ->setNormalizer(
                'cancelButtonDisplayed',
                function (Options $options, $value) {
                    return (bool) $value;
                }
            )
            ->setNormalizer(
                'oneClickEnabled',
                function (Options $options, $value) {
                    return (bool) $value;
                }
            )
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
                'fontFamily',
                function (Options $options, $value) {
                    return trim($value);
                }
            )
            ->setNormalizer(
                'fontSize',
                function (Options $options, $value) {
                    return (int) $value;
                }
            )
            ->setNormalizer(
                'fontWeight',
                function (Options $options, $value) {
                    return (int) $value;
                }
            )
            ->setNormalizer(
                'color',
                function (Options $options, $value) {
                    return trim($value);
                }
            )
            ->setNormalizer(
                'placeholderColor',
                function (Options $options, $value) {
                    return trim($value);
                }
            )
            ->setNormalizer(
                'caretColor',
                function (Options $options, $value) {
                    return trim($value);
                }
            )
            ->setNormalizer(
                'iconColor',
                function (Options $options, $value) {
                    return trim($value);
                }
            )
            ->setNormalizer(
                'oneClickHighlightColor',
                function (Options $options, $value) {
                    return trim($value);
                }
            );
    }
}
