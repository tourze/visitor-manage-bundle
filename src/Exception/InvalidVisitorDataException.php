<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Exception;

class InvalidVisitorDataException extends VisitorManageException
{
    /** @var array<string> */
    private array $violations;

    /**
     * @param array<string> $violations
     */
    public function __construct(array $violations, ?\Throwable $previous = null)
    {
        $this->violations = $violations;

        $message = '访客数据验证失败';
        if (count($violations) > 0) {
            $errors = implode(', ', $violations);
            $message .= ": {$errors}";
        }

        parent::__construct($message, 400, $previous);
    }

    /**
     * @return array<string>
     */
    public function getViolations(): array
    {
        return $this->violations;
    }
}
