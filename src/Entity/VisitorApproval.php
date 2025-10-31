<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\VisitorManageBundle\Enum\ApprovalStatus;
use Tourze\VisitorManageBundle\Repository\VisitorApprovalRepository;

/**
 * 访客审批实体（贫血模型）
 */
#[ORM\Entity(repositoryClass: VisitorApprovalRepository::class)]
#[ORM\Table(name: 'visitor_approval', options: ['comment' => '访客审批表'])]
class VisitorApproval implements \Stringable
{
    /** @phpstan-ignore property.unusedType */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键ID'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Visitor::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'visitor_id', referencedColumnName: 'id', nullable: false)]
    private ?Visitor $visitor = null;

    #[ORM\Column(type: Types::STRING, enumType: ApprovalStatus::class, options: ['comment' => '审批状态'])]
    #[Assert\Choice(callback: [ApprovalStatus::class, 'cases'], message: '审批状态无效')]
    #[IndexColumn]
    private ApprovalStatus $status = ApprovalStatus::PENDING;

    #[ORM\Column(name: 'approver_id', type: Types::INTEGER, nullable: true, options: ['comment' => '审批人ID'])]
    #[Assert\Positive(message: '审批人ID必须为正数')]
    private ?int $approver = null;

    #[ORM\Column(name: 'reject_reason', type: Types::TEXT, nullable: true, options: ['comment' => '拒绝原因'])]
    #[Assert\Length(max: 1000, maxMessage: '拒绝原因不能超过1000个字符')]
    private ?string $rejectReason = null;

    #[ORM\Column(name: 'approve_time', type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '审批时间'])]
    #[Assert\DateTime(message: '审批时间格式无效')]
    #[IndexColumn]
    private ?\DateTimeImmutable $approveTime = null;

    #[ORM\Column(name: 'create_time', type: Types::DATETIME_IMMUTABLE, options: ['comment' => '创建时间'])]
    private ?\DateTimeImmutable $createTime = null;

    public function __construct()
    {
        $this->createTime = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVisitor(): ?Visitor
    {
        return $this->visitor;
    }

    public function setVisitor(?Visitor $visitor): void
    {
        $this->visitor = $visitor;
    }

    public function getStatus(): ApprovalStatus
    {
        return $this->status;
    }

    public function setStatus(ApprovalStatus $status): void
    {
        $this->status = $status;
    }

    public function getApprover(): ?int
    {
        return $this->approver;
    }

    public function setApprover(?int $approver): void
    {
        $this->approver = $approver;
    }

    public function getRejectReason(): ?string
    {
        return $this->rejectReason;
    }

    public function setRejectReason(?string $rejectReason): void
    {
        $this->rejectReason = $rejectReason;
    }

    public function getApproveTime(): ?\DateTimeImmutable
    {
        return $this->approveTime;
    }

    public function setApproveTime(?\DateTimeImmutable $approveTime): void
    {
        $this->approveTime = $approveTime;
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
        return sprintf('审批 #%d (%s)', $this->id ?? 0, $this->status->value);
    }
}
