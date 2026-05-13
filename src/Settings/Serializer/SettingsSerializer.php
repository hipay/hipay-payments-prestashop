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

namespace HiPay\PrestaShop\Settings\Serializer;

if (!defined('_PS_VERSION_')) {
    exit;
}

use AG\PSModuleUtils\Settings\Serializer\AbstractSettingsSerializer;
use HiPay\PrestaShop\Settings\Entity\AbstractAdvancedPaymentMethod;
use HiPay\PrestaShop\Settings\Entity\AdvancedPaymentSettings;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorMapping;

/**
 * Class SettingsSerializer
 */
class SettingsSerializer extends AbstractSettingsSerializer
{
    /**
     * SettingsSerializer Constructor.
     */
    public function __construct()
    {
        $codes = array_keys(AdvancedPaymentSettings::APM_CODES);
        $classes = array_column(AdvancedPaymentSettings::APM_CODES, 'discriminatorMap');
        $discriminatorMap = (array) array_combine($codes, $classes);
        $discriminatorResolver = new SimpleClassDiscriminatorResolver();
        $discriminatorResolver->addClassMapping(
            AbstractAdvancedPaymentMethod::class,
            new ClassDiscriminatorMapping('code', $discriminatorMap)
        );

        $objectNormalizer = new ObjectNormalizer(
            null,
            null,
            null,
            new PhpDocExtractor(),
            $discriminatorResolver,
            null,
            []
        );

        $codeFixerNormalizer = new class($objectNormalizer) implements NormalizerInterface {
            /** @var ObjectNormalizer */
            private $innerNormalizer;

            /**
             * @param ObjectNormalizer $innerNormalizer
             */
            public function __construct(ObjectNormalizer $innerNormalizer)
            {
                $this->innerNormalizer = $innerNormalizer;
            }

            /**
             * @param AbstractAdvancedPaymentMethod $object
             * @param string $format
             * @param mixed[] $context
             * @return mixed[]|\ArrayObject|bool|float|int|mixed|string
             */
            public function normalize($object, $format = null, array $context = array())
            {
                $data = (array) $this->innerNormalizer->normalize($object, $format, $context);

                if ($object instanceof AbstractAdvancedPaymentMethod) {
                    $data['code'] = $object->code;
                }

                return $data;
            }

            /**
             * @param mixed $data
             * @param string $format
             * @return bool
             */
            public function supportsNormalization($data, $format = null): bool
            {
                return $data instanceof AbstractAdvancedPaymentMethod;
            }
        };

        $this->serializer = new Serializer(
            [$codeFixerNormalizer, $objectNormalizer, new ArrayDenormalizer()],
            [new SettingsJsonEncoder()]
        );
    }
}
