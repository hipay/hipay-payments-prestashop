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
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

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

        // Normalizer personnalisé qui gère la discrimination manuellement
        $customNormalizer = new class($discriminatorMap) implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface {
            /** @var mixed[] */
            private $discriminatorMap;

            /** @var ObjectNormalizer */
            private $objectNormalizer;

            /**
             * Constructor.
             *
             * @param mixed[] $discriminatorMap
             */
            public function __construct(array $discriminatorMap)
            {
                $this->discriminatorMap = $discriminatorMap;
                $this->objectNormalizer = new ObjectNormalizer(
                    null,
                    null,
                    null,
                    new PhpDocExtractor()
                );
            }

            /**
             * @param SerializerInterface $serializer
             * @return void
             */
            public function setSerializer(SerializerInterface $serializer): void
            {
                if ($this->objectNormalizer instanceof SerializerAwareInterface) {
                    $this->objectNormalizer->setSerializer($serializer);
                }
            }

            /**
             * @param mixed $object
             * @param string $format
             * @param mixed[] $context
             * @return mixed[]
             */
            public function normalize($object, $format = null, array $context = array()): array
            {
                $data = (array) $this->objectNormalizer->normalize($object, $format, $context);

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
            public function supportsNormalization($data, $format = null)
            {
                return $data instanceof AbstractAdvancedPaymentMethod;
            }

            /**
             * @param mixed $data
             * @param string $type
             * @param string $format
             * @param mixed[] $context
             * @return mixed
             */
            public function denormalize($data, $type, $format = null, array $context = array())
            {
                if ($type === AbstractAdvancedPaymentMethod::class || is_subclass_of($type, AbstractAdvancedPaymentMethod::class)) {
                    if (isset($data['code']) && isset($this->discriminatorMap[$data['code']])) {
                        $concreteClass = $this->discriminatorMap[$data['code']];
                        return $this->objectNormalizer->denormalize($data, $concreteClass, $format, $context);
                    }
                }

                return $this->objectNormalizer->denormalize($data, $type, $format, $context);
            }

            /**
             * @param mixed $data
             * @param string $type
             * @param string $format
             * @return bool
             */
            public function supportsDenormalization($data, $type, $format = null)
            {
                // Supporter la dénormalisation des classes du mapping
                if ($type === AbstractAdvancedPaymentMethod::class) {
                    return true;
                }

                return in_array($type, $this->discriminatorMap, true);
            }
        };

        $this->serializer = new Serializer(
            [$customNormalizer, new ObjectNormalizer(null, null, null, new PhpDocExtractor()), new ArrayDenormalizer()],
            [new SettingsJsonEncoder()]
        );
    }
}
