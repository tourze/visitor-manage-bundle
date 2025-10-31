<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class VisitorRegistrationData
{
    #[Assert\NotBlank(message: '访客姓名不能为空')]
    #[Assert\Length(max: 50, maxMessage: '访客姓名不能超过50个字符')]
    public ?string $name = null;

    #[Assert\NotBlank(message: '手机号码不能为空')]
    #[Assert\Regex(pattern: '/^1[3-9]\d{9}$/', message: '请输入有效的手机号码')]
    public ?string $mobile = null;

    #[Assert\NotBlank(message: '所属公司不能为空')]
    #[Assert\Length(max: 100, maxMessage: '公司名称不能超过100个字符')]
    public ?string $company = null;

    #[Assert\NotBlank(message: '拜访原因不能为空')]
    #[Assert\Length(max: 500, maxMessage: '拜访原因不能超过500个字符')]
    public ?string $reason = null;

    #[Assert\Length(max: 20, maxMessage: '车牌号不能超过20个字符')]
    public ?string $vehicleNumber = null;

    #[Assert\NotBlank(message: '预约时间不能为空')]
    #[Assert\GreaterThan(value: 'now', message: '预约时间必须是未来时间')]
    public ?\DateTime $appointmentTime = null;

    public ?int $bizUserId = null;

    #[Assert\Length(max: 100, maxMessage: '联系人不能超过100个字符')]
    public ?string $contactPerson = null;

    #[Assert\Length(max: 20, maxMessage: '身份证号不能超过20个字符')]
    public ?string $idCard = null;
}
