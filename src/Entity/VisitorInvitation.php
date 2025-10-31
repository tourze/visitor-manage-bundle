<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\VisitorManageBundle\Enum\InvitationStatus;
use Tourze\VisitorManageBundle\Repository\VisitorInvitationRepository;

/**
 * 访客邀请实体（贫血模型）
 * 只包含数据和getter/setter，不包含业务逻辑
 */
#[ORM\Entity(repositoryClass: VisitorInvitationRepository::class)]
#[ORM\Table(name: 'visitor_invitation', options: ['comment' => '访客邀请表'])]
class VisitorInvitation implements \Stringable
{
    /** @phpstan-ignore property.unusedType */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键ID'])]
    private ?int $id = null;

    #[ORM\Column(name: 'inviter_id', type: Types::INTEGER, nullable: false, options: ['comment' => '邀请者ID'])]
    #[Assert\NotBlank(message: '邀请者ID不能为空')]
    #[Assert\Positive(message: '邀请者ID必须为正数')]
    private ?int $inviter = null;

    #[ORM\OneToOne(targetEntity: Visitor::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'visitor_id', referencedColumnName: 'id', nullable: false, unique: true)]
    private ?Visitor $visitor = null;

    #[ORM\Column(name: 'invite_code', type: Types::STRING, length: 50, unique: true, options: ['comment' => '邀请码'])]
    #[Assert\NotBlank(message: '邀请码不能为空')]
    #[Assert\Length(max: 50, maxMessage: '邀请码不能超过50个字符')]
    #[IndexColumn]
    private string $inviteCode = '';

    #[ORM\Column(type: Types::STRING, enumType: InvitationStatus::class, options: ['comment' => '邀请状态'])]
    #[Assert\Choice(callback: [InvitationStatus::class, 'cases'], message: '邀请状态无效')]
    #[IndexColumn]
    private InvitationStatus $status = InvitationStatus::PENDING;

    #[ORM\Column(name: 'expire_time', type: Types::DATETIME_IMMUTABLE, options: ['comment' => '过期时间'])]
    #[Assert\NotNull(message: '过期时间不能为空')]
    #[IndexColumn]
    private ?\DateTimeImmutable $expireTime = null;

    #[ORM\Column(name: 'create_time', type: Types::DATETIME_IMMUTABLE, options: ['comment' => '创建时间'])]
    #[Assert\NotNull(message: '创建时间不能为空')]
    private ?\DateTimeImmutable $createTime = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInviter(): ?int
    {
        return $this->inviter;
    }

    public function setInviter(?int $inviter): void
    {
        $this->inviter = $inviter;
    }

    public function getVisitor(): ?Visitor
    {
        return $this->visitor;
    }

    public function setVisitor(?Visitor $visitor): void
    {
        $this->visitor = $visitor;
    }

    public function getInviteCode(): string
    {
        return $this->inviteCode;
    }

    public function setInviteCode(string $inviteCode): void
    {
        $this->inviteCode = $inviteCode;
    }

    public function getStatus(): InvitationStatus
    {
        return $this->status;
    }

    public function setStatus(InvitationStatus $status): void
    {
        $this->status = $status;
    }

    public function getExpireTime(): ?\DateTimeImmutable
    {
        return $this->expireTime;
    }

    public function setExpireTime(\DateTimeImmutable $expireTime): void
    {
        $this->expireTime = $expireTime;
    }

    public function setExpiresAt(\DateTimeInterface $expiresAt): void
    {
        if ($expiresAt instanceof \DateTime) {
            $this->expireTime = \DateTimeImmutable::createFromMutable($expiresAt);
        } else {
            $this->expireTime = $expiresAt;
        }
    }

    public function getCreateTime(): ?\DateTimeImmutable
    {
        return $this->createTime;
    }

    public function setCreateTime(\DateTimeImmutable $createTime): void
    {
        $this->createTime = $createTime;
    }

    public function __toString(): string
    {
        return sprintf('邀请码: %s (%s)', $this->inviteCode, $this->status->value);
    }
}
