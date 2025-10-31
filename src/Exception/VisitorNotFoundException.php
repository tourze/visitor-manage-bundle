<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Exception;

class VisitorNotFoundException extends VisitorManageException
{
    public function __construct(int $id, ?string $message = null, ?\Throwable $previous = null)
    {
        $message ??= "访客不存在，ID: {$id}";
        parent::__construct($message, 404, $previous);
    }
}
