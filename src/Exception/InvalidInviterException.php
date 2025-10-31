<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Exception;

class InvalidInviterException extends \InvalidArgumentException
{
    public function __construct(string $message = '邀请者无效', ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
