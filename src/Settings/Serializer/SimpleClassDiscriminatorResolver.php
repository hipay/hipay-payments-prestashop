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

use Symfony\Component\Serializer\Mapping\ClassDiscriminatorMapping;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorResolverInterface;

/**
 * Simple implementation of ClassDiscriminatorResolverInterface
 */
class SimpleClassDiscriminatorResolver implements ClassDiscriminatorResolverInterface
{
    /** @var mixed[] */
    private $mappings = [];

    /**
     * @param string $class
     * @param ClassDiscriminatorMapping $mapping
     * @return void
     */
    public function addClassMapping(string $class, ClassDiscriminatorMapping $mapping): void
    {
        $this->mappings[$class] = $mapping;
    }

    /**
     * @param string $class
     * @return ClassDiscriminatorMapping|null
     */
    public function getMappingForClass(string $class): ?ClassDiscriminatorMapping
    {
        return $this->mappings[$class] ?? null;
    }

    /**
     * @param mixed $object
     * @return ClassDiscriminatorMapping|null
     */
    public function getMappingForMappedObject($object): ?ClassDiscriminatorMapping
    {
        $class = is_object($object) ? get_class($object) : $object;

        // Check if the class itself has a mapping
        if (isset($this->mappings[$class])) {
            return $this->mappings[$class];
        }

        // Check parent classes
        foreach ($this->mappings as $mappedClass => $mapping) {
            if (is_a($class, $mappedClass, true)) {
                return $mapping;
            }
        }

        return null;
    }

    /**
     * @param mixed $object
     * @return string|null
     */
    public function getTypeForMappedObject($object): ?string
    {
        $mapping = $this->getMappingForMappedObject($object);

        if (null === $mapping) {
            return null;
        }

        return $mapping->getMappedObjectType($object);
    }
}
