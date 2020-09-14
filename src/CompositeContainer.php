<?php

namespace Dhii\Container;

use Dhii\Collection\ContainerInterface;
use Dhii\Container\Exception\ContainerException;
use Dhii\Container\Exception\NotFoundException;
use Dhii\Container\Util\StringTranslatingTrait;
use Exception;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Traversable;
use UnexpectedValueException;

class CompositeContainer implements ContainerInterface
{
    use StringTranslatingTrait;

    /**
     * @var array|PsrContainerInterface[]|Traversable
     */
    protected $containers;

    /**
     * @param PsrContainerInterface[]|Traversable $containers The list of containers.
     */
    public function __construct($containers)
    {
        if (!is_array($containers) && !($containers instanceof Traversable)) {
            throw new UnexpectedValueException(
                $this->__('The containers argument is not a valid list')
            );
        }
        $this->containers = $containers;
    }

    /**
     * {@inheritDoc}
     */
    public function get($key)
    {
        $key = (string) $key;

        foreach ($this->containers as $index => $container) {
            try {
                if ($container->has($key)) {
                    return $container->get($key);
                }
            } catch (NotFoundExceptionInterface $e) {
                throw new NotFoundException(
                    $this->__('Failed to retrieve value for key "%1$s" from container at index "%2$s"', [$key, $index]),
                    0,
                    $e
                );
            } catch (Exception $e) {
                throw new ContainerException(
                    $this->__('Failed check for key "%1$s" on container at index "%2$s"', [$key, $index]),
                    0,
                    $e
                );
            }
        }

        throw new NotFoundException(
            $this->__('Key "%1$s" not found in any of the containers', [$key]),
            0,
            null
        );
    }

    /**
     * {@inheritDoc}
     */
    public function has($key)
    {
        $key = (string) $key;

        foreach ($this->containers as $index => $container) {
            try {
                if ($container->has($key)) {
                    return true;
                }
            } catch (Exception $e) {
                throw new ContainerException(
                    $this->__('Failed check for key "%1$s" on container at index "%2$s"', [$key, $index]),
                    0,
                    $e
                );
            }
        }

        return false;
    }
}
