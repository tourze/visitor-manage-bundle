<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Exception;

class QrCodeGenerationException extends VisitorManageException
{
    public function __construct(string $message = '二维码生成失败', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
