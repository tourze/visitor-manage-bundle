<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 邀请状态枚举
 */
enum InvitationStatus: string implements Itemable, Labelable, Selectable
{
    use SelectTrait;
    use ItemTrait;

    case PENDING = 'pending';         // 待确认
    case CONFIRMED = 'confirmed';     // 已确认
    case REJECTED = 'rejected';       // 已拒绝
    case EXPIRED = 'expired';         // 已过期

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => '待确认',
            self::CONFIRMED => '已确认',
            self::REJECTED => '已拒绝',
            self::EXPIRED => '已过期',
        };
    }
}
