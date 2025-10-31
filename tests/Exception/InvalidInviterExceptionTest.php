<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\VisitorManageBundle\Exception\InvalidInviterException;

/**
 * @internal
 */
#[CoversClass(InvalidInviterException::class)]
class InvalidInviterExceptionTest extends AbstractExceptionTestCase
{
}
