<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 审批状态枚举
 */
enum ApprovalStatus: string implements Itemable, Labelable, Selectable
{
    use SelectTrait;
    use ItemTrait;

    case PENDING = 'pending';         // 待审批
    case APPROVED = 'approved';       // 已通过
    case REJECTED = 'rejected';       // 已拒绝

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => '待审批',
            self::APPROVED => '已通过',
            self::REJECTED => '已拒绝',
        };
    }
}
