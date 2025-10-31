<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 访客状态枚举
 */
enum VisitorStatus: string implements Itemable, Labelable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    case PENDING = 'pending';         // 待审批
    case APPROVED = 'approved';       // 已审批
    case REJECTED = 'rejected';       // 已拒绝
    case SIGNED_IN = 'signed_in';     // 已签入
    case SIGNED_OUT = 'signed_out';   // 已签出
    case CANCELLED = 'cancelled';     // 已取消

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => '待审批',
            self::APPROVED => '已审批',
            self::REJECTED => '已拒绝',
            self::SIGNED_IN => '已签入',
            self::SIGNED_OUT => '已签出',
            self::CANCELLED => '已取消',
        };
    }
}
