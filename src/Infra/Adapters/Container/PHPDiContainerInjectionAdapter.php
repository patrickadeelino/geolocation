<?php

use Psr\Container\ContainerInterface;

class PHPDiContainerInjectionAdapter implements ContainerInterface
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @template T of object
     * @param class-string<T> $id
     * @return T
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function get(string $id): object
    {
        $return = $this->container->get($id);
        if (!\is_object($return)) {
            throw new \InvalidArgumentException(
                sprintf('Unable to find definition for provided argument %s', $id)
            );
        }

        /**
         * @var T $return
         */
        return $return;
    }

    public function has(string $id)
    {
        return $this->container->has($id);
    }
}