<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 访客操作枚举
 */
enum VisitorAction: string implements Itemable, Labelable, Selectable
{
    use SelectTrait;
    use ItemTrait;

    case REGISTERED = 'registered';         // 已注册
    case APPROVED = 'approved';             // 已审批
    case REJECTED = 'rejected';             // 已拒绝
    case SIGNED_IN = 'signed_in';           // 已签入
    case SIGNED_OUT = 'signed_out';         // 已签出
    case CANCELLED = 'cancelled';           // 已取消
    case BULK_APPROVED = 'bulk_approved';   // 批量审批
    case BULK_REJECTED = 'bulk_rejected';   // 批量拒绝
    case ERROR = 'error';                   // 错误记录
    case PASS_GENERATED = 'pass_generated'; // 通行证已生成
    case PASS_USED = 'pass_used';           // 通行证已使用
    case OTHER = 'other';                   // 其他操作

    public function getLabel(): string
    {
        return match ($this) {
            self::REGISTERED => '已注册',
            self::APPROVED => '已审批',
            self::REJECTED => '已拒绝',
            self::SIGNED_IN => '已签入',
            self::SIGNED_OUT => '已签出',
            self::CANCELLED => '已取消',
            self::BULK_APPROVED => '批量审批',
            self::BULK_REJECTED => '批量拒绝',
            self::ERROR => '错误记录',
            self::PASS_GENERATED => '通行证已生成',
            self::PASS_USED => '通行证已使用',
            self::OTHER => '其他操作',
        };
    }
}
