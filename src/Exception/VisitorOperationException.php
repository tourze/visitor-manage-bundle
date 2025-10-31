<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Exception;

use RuntimeException;

class VisitorOperationException extends \RuntimeException
{
    public function __construct(string $message = '', ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    public static function databaseError(string $message, ?\Throwable $previous = null): self
    {
        return new self($message, $previous);
    }

    public static function validationError(string $message, ?\Throwable $previous = null): self
    {
        return new self($message, $previous);
    }

    public static function operationFailed(string $message, ?\Throwable $previous = null): self
    {
        return new self($message, $previous);
    }
}
