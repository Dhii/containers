<?php

namespace Dhii\Container\Exception;

use Dhii\Data\Container\Exception\NotFoundExceptionInterface;
use Psr\Container\ContainerInterface;
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
        Throwable $previous = null,
        ContainerInterface $container = null,
        string $dataKey = null
    ) {
        parent::__construct($message, $code, $previous, $container);
        $this->dataKey = $dataKey;
    }


    /**
     * {@inheritdoc
     */
    public function getDataKey()
    {
        return $this->dataKey;
    }
}