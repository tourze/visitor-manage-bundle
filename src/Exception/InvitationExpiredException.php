<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Exception;

class InvitationExpiredException extends \RuntimeException
{
    public function __construct(string $message = '邀请已过期', ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
