<?php

namespace Dhii\Container\Exception;

use Psr\Container\NotFoundExceptionInterface;
use Throwable;

class NotFoundException extends ContainerException implements NotFoundExceptionInterface
{
    /**
     * @var string
     */
    protected $dataKey;

    public function __construct(
        $message = '',
        $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
