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
 * Class AccountSettingsOptionsResolver
 */
class AccountSettingsOptionsResolver extends AbstractSettingsResolver
{
    /**
     * @param mixed[] $parameters
     * @return mixed[]
     */
    public function resolve($parameters): array
    {
        $resolvedArray = $this->resolver->resolve($parameters);
        if (!empty($resolvedArray['testPrivateIdentifiers'])) {
            $resolvedArray['testPrivateIdentifiers'] = $this->resolver->resolve($parameters['testPrivateIdentifiers']);
        }
        if (!empty($resolvedArray['testPublicIdentifiers'])) {
            $resolvedArray['testPublicIdentifiers'] = $this->resolver->resolve($parameters['testPublicIdentifiers']);
        }
        if (!empty($resolvedArray['prodPrivateIdentifiers'])) {
            $resolvedArray['prodPrivateIdentifiers'] = $this->resolver->resolve($parameters['prodPrivateIdentifiers']);
        }
        if (!empty($resolvedArray['prodPublicIdentifiers'])) {
            $resolvedArray['prodPublicIdentifiers'] = $this->resolver->resolve($parameters['prodPublicIdentifiers']);
        }
        if (!empty($resolvedArray['applePayTestPrivateIdentifiers'])) {
            $resolvedArray['applePayTestPrivateIdentifiers'] = $this->resolver->resolve($parameters['applePayTestPrivateIdentifiers']);
        }
        if (!empty($resolvedArray['applePayTestPublicIdentifiers'])) {
            $resolvedArray['applePayTestPublicIdentifiers'] = $this->resolver->resolve($parameters['applePayTestPublicIdentifiers']);
        }
        if (!empty($resolvedArray['applePayProdPrivateIdentifiers'])) {
            $resolvedArray['applePayProdPrivateIdentifiers'] = $this->resolver->resolve($parameters['applePayProdPrivateIdentifiers']);
        }
        if (!empty($resolvedArray['applePayProdPublicIdentifiers'])) {
            $resolvedArray['applePayProdPublicIdentifiers'] = $this->resolver->resolve($parameters['applePayProdPublicIdentifiers']);
        }
        if (!empty($resolvedArray['motoTestPrivateIdentifiers'])) {
            $resolvedArray['motoTestPrivateIdentifiers'] = $this->resolver->resolve($parameters['motoTestPrivateIdentifiers']);
        }
        if (!empty($resolvedArray['motoTestPublicIdentifiers'])) {
            $resolvedArray['motoTestPublicIdentifiers'] = $this->resolver->resolve($parameters['motoTestPublicIdentifiers']);
        }
        if (!empty($resolvedArray['motoProdPrivateIdentifiers'])) {
            $resolvedArray['motoProdPrivateIdentifiers'] = $this->resolver->resolve($parameters['motoProdPrivateIdentifiers']);
        }
        if (!empty($resolvedArray['motoProdPublicIdentifiers'])) {
            $resolvedArray['motoProdPublicIdentifiers'] = $this->resolver->resolve($parameters['motoProdPublicIdentifiers']);
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
                'hashingAlgorithms',
                'useDemoMode',
                'environment',
                'testPrivateIdentifiers',
                'testPublicIdentifiers',
                'prodPrivateIdentifiers',
                'prodPublicIdentifiers',
                'applePayTestPrivateIdentifiers',
                'applePayTestPublicIdentifiers',
                'applePayProdPrivateIdentifiers',
                'applePayProdPublicIdentifiers',
                'motoTestPrivateIdentifiers',
                'motoProdPrivateIdentifiers',
                'username',
                'password',
                'secret',
            ])
            ->setNormalizer(
                'useDemoMode',
                function (Options $options, $value) {
                    return (bool) $value;
                }
            )
            ->setNormalizer(
                'hashingAlgorithms',
                function (Options $options, $value) {
                    return '[]' === $value ? [] : array_map('trim', $value);
                }
            )
            ->setNormalizer(
                'environment',
                function (Options $options, $value) {
                    return trim($value);
                }
            )
            ->setNormalizer(
                'username',
                function (Options $options, $value) {
                    return trim($value);
                }
            )
            ->setNormalizer(
                'password',
                function (Options $options, $value) {
                    return trim($value);
                }
            )
            ->setNormalizer(
                'secret',
                function (Options $options, $value) {
                    return trim($value);
                }
            );
    }
}
