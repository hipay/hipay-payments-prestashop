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
 * Class MainSettingsOptionsResolver
 */
class MainSettingsOptionsResolver extends AbstractSettingsResolver
{
    /**
     * @param mixed[] $parameters
     * @return mixed[]
     */
    public function resolve($parameters): array
    {
        return $this->resolver->resolve($parameters);
    }

    /**
     * @param OptionsResolver $resolver
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefined([
                'verboseLogsEnabled',
                'captureMode',
            ])
            ->setNormalizer(
                'verboseLogsEnabled',
                function (Options $options, $value) {
                    return (bool) $value;
                }
            )
            ->setNormalizer(
                'captureMode',
                function (Options $options, $value) {
                    return trim($value);
                }
            );
    }
}
