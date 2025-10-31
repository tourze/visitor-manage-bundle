<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\VisitorManageBundle\Enum\VisitorAction;
use Tourze\VisitorManageBundle\Repository\VisitorLogRepository;

/**
 * 访客日志实体（贫血模型）
 */
#[ORM\Entity(repositoryClass: VisitorLogRepository::class)]
#[ORM\Table(name: 'visitor_log', options: ['comment' => '访客日志表'])]
class VisitorLog implements \Stringable
{
    /** @phpstan-ignore property.unusedType */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键ID'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Visitor::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'visitor_id', referencedColumnName: 'id', nullable: true)]
    private ?Visitor $visitor = null;

    #[ORM\Column(type: Types::STRING, enumType: VisitorAction::class, options: ['comment' => '操作类型'])]
    #[Assert\Choice(callback: [VisitorAction::class, 'cases'], message: '操作类型无效')]
    #[IndexColumn]
    private ?VisitorAction $action = null;

    #[ORM\Column(name: 'operator_id', type: Types::INTEGER, nullable: true, options: ['comment' => '操作人ID'])]
    #[Assert\Positive(message: '操作人ID必须为正数')]
    private ?int $operator = null;

    #[ORM\Column(type: Types::TEXT, options: ['comment' => '备注'])]
    #[Assert\Length(max: 1000, maxMessage: '备注不能超过1000个字符')]
    private string $remark = '';

    #[ORM\Column(name: 'create_time', type: Types::DATETIME_IMMUTABLE, options: ['comment' => '创建时间'])]
    #[Assert\NotNull(message: '创建时间不能为空')]
    #[IndexColumn]
    private ?\DateTimeImmutable $createTime = null;

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

    public function getAction(): ?VisitorAction
    {
        return $this->action;
    }

    public function setAction(?VisitorAction $action): void
    {
        $this->action = $action;
    }

    public function getOperator(): ?int
    {
        return $this->operator;
    }

    public function setOperator(?int $operator): void
    {
        $this->operator = $operator;
    }

    public function getRemark(): string
    {
        return $this->remark;
    }

    public function setRemark(string $remark): void
    {
        $this->remark = $remark;
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
        return sprintf('日志 #%d: %s', $this->id ?? 0, $this->action->value ?? 'unknown');
    }
}
