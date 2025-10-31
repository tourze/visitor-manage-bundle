<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\VisitorManageBundle\Exception\VisitorManageException;

/**
 * @internal
 */
#[CoversClass(VisitorManageException::class)]
class VisitorManageExceptionTest extends AbstractExceptionTestCase
{
}
