<?php
/*
 * MIT License
 *
 * Copyright (c) 2022 Anthony Girard
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 */

namespace AG\PSModuleUtils\Settings;

use AG\PSModuleUtils\Exception\ExceptionList;
use AG\PSModuleUtils\Settings\OptionsResolver\AbstractSettingsResolver;
use AG\PSModuleUtils\Settings\Validation\AbstractValidationData;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;

abstract class AbstractSettingsUpdater
{
    protected string $json;
    private ConstraintViolationListInterface $violations;

    public function __construct(
        protected Serializer $serializer,
        protected AbstractSettingsResolver $resolver,
        protected AbstractSettings $settings,
        protected AbstractValidationData $validationData,
        protected \Module $module
    ) {
    }

    /**
     * @throws ExceptionList
     */
    public function update(array $array): AbstractSettings
    {
        $array = $this->resolver->resolve($array);
        $this->validate($array);
        $this->denormalize($array);
        $this->serialize();
        $this->save();

        return $this->settings;
    }

    /**
     * @throws ExceptionList|\Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function updateObject(mixed $object = null): AbstractSettings
    {
        $array = $this->serializer->normalize($object);

        return $this->update($array);
    }

    /**
     * @throws ExceptionList
     */
    public function validate(array $array): void
    {
        $validationData = $this->validationData->getValidationData($array);
        $validator = Validation::createValidator();
        $constraints = new Collection($validationData['constraints']);
        $this->violations = $validator->validate($validationData['array'], $constraints);
        $exceptions = [];
        foreach ($this->violations as $violation) {
            $exceptions[] = new \Exception($violation->getMessage());
        }

        if (!empty($exceptions)) {
            $exceptionList = new ExceptionList('Error while validating account settings data');
            $exceptionList->setExceptions($exceptions);
            throw $exceptionList;
        }
    }

    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }

    abstract protected function denormalize(array $array): void;

    abstract protected function serialize(): void;

    abstract protected function save(): void;
}
