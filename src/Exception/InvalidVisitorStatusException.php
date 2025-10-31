<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Exception;

class InvalidVisitorStatusException extends \InvalidArgumentException
{
    public function __construct(string $message = '访客状态不允许该操作', ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
