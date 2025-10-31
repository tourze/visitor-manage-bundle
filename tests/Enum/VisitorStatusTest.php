<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;
use Tourze\VisitorManageBundle\Enum\VisitorStatus;

/**
 * @internal
 */
#[CoversClass(VisitorStatus::class)]
class VisitorStatusTest extends AbstractEnumTestCase
{
    // AbstractEnumTestCase 已经提供了所有基础的枚举测试
    // 包括测试枚举值、标签、from/tryFrom 方法等

    public function testToArray(): void
    {
        foreach (VisitorStatus::cases() as $status) {
            $array = $status->toArray();
            $this->assertArrayHasKey('value', $array);
            $this->assertArrayHasKey('label', $array);
            $this->assertEquals($status->value, $array['value']);
            $this->assertEquals($status->getLabel(), $array['label']);
        }
    }
}
