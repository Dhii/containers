<?php

declare(strict_types=1);

namespace Dhii\Container\Exception;

use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Throwable;

/**
 * Basic implementation of container exception.
 *
 * @package Dhii\Di
 */
class ContainerException extends Exception implements ContainerExceptionInterface
{
    protected ?ContainerInterface $container = null;

    /**
     * @param string         $message  The exception message.
     * @param int            $code     The exception code.
     * @param ?Throwable     $previous The inner exception, if any.
     */
    public function __construct(
        string $message = "",
        int $code = 0,
        ?Throwable $previous = null,
        ?ContainerInterface $container = null
    ) {
        parent::__construct($message, $code, $previous);
        // TODO: Implement getter
        $this->container = $container;
    }
}
