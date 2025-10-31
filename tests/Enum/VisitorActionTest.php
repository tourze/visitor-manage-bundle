<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;
use Tourze\VisitorManageBundle\Enum\VisitorAction;

/**
 * @internal
 */
#[CoversClass(VisitorAction::class)]
class VisitorActionTest extends AbstractEnumTestCase
{
    // AbstractEnumTestCase 已经提供了所有基础的枚举测试
    // 包括测试枚举值、标签、from/tryFrom 方法等

    public function testToArray(): void
    {
        foreach (VisitorAction::cases() as $action) {
            $array = $action->toArray();
            $this->assertArrayHasKey('value', $array);
            $this->assertArrayHasKey('label', $array);
            $this->assertEquals($action->value, $array['value']);
            $this->assertEquals($action->getLabel(), $array['label']);
        }
    }
}
