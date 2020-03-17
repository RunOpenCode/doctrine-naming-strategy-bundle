<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\DoctrineNamingStrategy\Exception;

class RuntimeException extends \RuntimeException implements ExceptionInterface
{
    public function __construct(string $message, \Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
