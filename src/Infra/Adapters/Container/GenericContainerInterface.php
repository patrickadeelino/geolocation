<?php

namespace Infra\Adapters\Container;

use Psr\Container\ContainerInterface;

/**
 * This class decorates PHPDI ContainerInterface adding the phpstan's generic return behavior.
 * It will help with the IDE auto complete and fix phpstan error messages "expects Foo\Bar\Class, mixed given".
 */
interface GenericContainerInterface extends ContainerInterface
{
    /**
     * @template T of object
     * @param class-string<T> $id
     * @return T
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function get(string $id);
}