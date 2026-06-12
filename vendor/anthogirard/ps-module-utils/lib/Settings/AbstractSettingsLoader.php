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

use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Serializer;

/**
 * @template T of AbstractSettings
 */
abstract class AbstractSettingsLoader
{
    protected ?int $idShop = null;
    protected ?int $idShopGroup = null;

    public function __construct(protected readonly Serializer $serializer)
    {
    }

    /**
     * @return T
     */
    abstract protected function deserialize(): AbstractSettings;

    /**
     * @return T
     */
    public function load(): AbstractSettings
    {
        $settings = $this->deserialize();

        return $settings->postLoading();
    }

    /**
     * @throws ExceptionInterface
     */
    public function normalize(): array
    {
        $settings = $this->deserialize();

        return $this->serializer->normalize($settings);
    }

    /**
     * @deprecated Use withContext() instead.
     * @return T
     */
    public function setContext(?int $idShop = null, ?int $idShopGroup = null): AbstractSettings
    {
        $this->idShop = (int) $idShop;
        $this->idShopGroup = (int) $idShopGroup;

        return $this->load();
    }

    /**
     * @return T
     */
    public function withContext(?int $idShop = null, ?int $idShopGroup = null, bool $force = false): AbstractSettings
    {
        $this->idShop = true === $force ? $idShop : \Context::getContext()->shop->id;
        $this->idShopGroup = true === $force ? $idShopGroup : \Context::getContext()->shop->id_shop_group;

        return $this->load();
    }
}
