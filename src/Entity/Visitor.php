<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\VisitorManageBundle\Enum\VisitorStatus;
use Tourze\VisitorManageBundle\Repository\VisitorRepository;

/**
 * 访客实体（贫血模型）
 * 只包含数据和getter/setter，不包含业务逻辑
 */
#[ORM\Entity(repositoryClass: VisitorRepository::class)]
#[ORM\Table(name: 'visitor', options: ['comment' => '访客信息表'])]
class Visitor implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键ID'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '访客姓名'])]
    #[Assert\NotBlank(message: '姓名不能为空')]
    #[Assert\Length(max: 100, maxMessage: '姓名不能超过100个字符')]
    private string $name = '';

    #[ORM\Column(type: Types::STRING, length: 15, options: ['comment' => '手机号码'])]
    #[Assert\NotBlank(message: '手机号码不能为空')]
    #[Assert\Length(max: 15, maxMessage: '手机号码不能超过15个字符')]
    #[Assert\Regex(pattern: '/^1[3-9]\d{9}$/', message: '手机号码格式不正确')]
    #[IndexColumn]
    private string $mobile = '';

    #[ORM\Column(type: Types::STRING, length: 200, options: ['comment' => '公司名称'])]
    #[Assert\Length(max: 200, maxMessage: '公司名称不能超过200个字符')]
    private string $company = '';

    #[ORM\Column(type: Types::TEXT, options: ['comment' => '来访事由'])]
    #[Assert\Length(max: 1000, maxMessage: '来访事由不能超过1000个字符')]
    private string $reason = '';

    #[ORM\Column(name: 'vehicle_number', type: Types::STRING, length: 20, nullable: true, options: ['comment' => '车牌号'])]
    #[Assert\Length(max: 20, maxMessage: '车牌号不能超过20个字符')]
    private ?string $vehicleNumber = null;

    #[ORM\Column(name: 'contact_person', type: Types::STRING, length: 100, nullable: true, options: ['comment' => '联系人'])]
    #[Assert\Length(max: 100, maxMessage: '联系人不能超过100个字符')]
    private ?string $contactPerson = null;

    #[ORM\Column(name: 'id_card', type: Types::STRING, length: 20, nullable: true, options: ['comment' => '身份证号'])]
    #[Assert\Length(max: 20, maxMessage: '身份证号不能超过20个字符')]
    private ?string $idCard = null;

    #[ORM\Column(name: 'biz_user_id', type: Types::INTEGER, nullable: true, options: ['comment' => '关联的用户ID'])]
    #[Assert\Positive(message: '关联的用户ID必须为正数')]
    private ?int $bizUserId = null;

    #[ORM\Column(name: 'appointment_time', type: Types::DATETIME_IMMUTABLE, options: ['comment' => '预约时间'])]
    #[Assert\NotNull(message: '预约时间不能为空')]
    #[Assert\GreaterThan(value: 'today', message: '预约时间不能早于今天')]
    #[IndexColumn]
    private ?\DateTimeImmutable $appointmentTime = null;

    #[ORM\Column(type: Types::STRING, enumType: VisitorStatus::class, options: ['comment' => '访客状态'])]
    #[Assert\Choice(callback: [VisitorStatus::class, 'cases'], message: '访客状态无效')]
    #[IndexColumn]
    private VisitorStatus $status = VisitorStatus::PENDING;

    #[ORM\Column(name: 'sign_in_time', type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '签到时间'])]
    #[Assert\DateTime(message: '签到时间格式无效')]
    private ?\DateTimeImmutable $signInTime = null;

    #[ORM\Column(name: 'sign_out_time', type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '签退时间'])]
    #[Assert\DateTime(message: '签退时间格式无效')]
    private ?\DateTimeImmutable $signOutTime = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getMobile(): string
    {
        return $this->mobile;
    }

    public function setMobile(string $mobile): void
    {
        $this->mobile = $mobile;
    }

    public function getCompany(): string
    {
        return $this->company;
    }

    public function setCompany(string $company): void
    {
        $this->company = $company;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function setReason(string $reason): void
    {
        $this->reason = $reason;
    }

    public function getVehicleNumber(): ?string
    {
        return $this->vehicleNumber;
    }

    public function setVehicleNumber(?string $vehicleNumber): void
    {
        $this->vehicleNumber = $vehicleNumber;
    }

    public function getContactPerson(): ?string
    {
        return $this->contactPerson;
    }

    public function setContactPerson(?string $contactPerson): void
    {
        $this->contactPerson = $contactPerson;
    }

    public function getIdCard(): ?string
    {
        return $this->idCard;
    }

    public function setIdCard(?string $idCard): void
    {
        $this->idCard = $idCard;
    }

    public function getBizUserId(): ?int
    {
        return $this->bizUserId;
    }

    public function setBizUserId(?int $bizUserId): void
    {
        $this->bizUserId = $bizUserId;
    }

    public function getAppointmentTime(): ?\DateTimeImmutable
    {
        return $this->appointmentTime;
    }

    public function setAppointmentTime(\DateTimeImmutable $appointmentTime): void
    {
        $this->appointmentTime = $appointmentTime;
    }

    public function getStatus(): VisitorStatus
    {
        return $this->status;
    }

    public function setStatus(VisitorStatus $status): void
    {
        $this->status = $status;
    }

    public function getSignInTime(): ?\DateTimeImmutable
    {
        return $this->signInTime;
    }

    public function setSignInTime(?\DateTimeImmutable $signInTime): void
    {
        $this->signInTime = $signInTime;
    }

    public function getSignOutTime(): ?\DateTimeImmutable
    {
        return $this->signOutTime;
    }

    public function setSignOutTime(?\DateTimeImmutable $signOutTime): void
    {
        $this->signOutTime = $signOutTime;
    }

    public function __toString(): string
    {
        return sprintf('%s (%s)', $this->name, $this->mobile);
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }
}
