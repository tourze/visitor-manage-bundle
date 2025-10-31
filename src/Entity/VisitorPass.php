<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\VisitorManageBundle\Repository\VisitorPassRepository;

/**
 * 访客通行码实体（贫血模型）
 */
#[ORM\Entity(repositoryClass: VisitorPassRepository::class)]
#[ORM\Table(name: 'visitor_pass', options: ['comment' => '访客通行码表'])]
#[ORM\Index(columns: ['valid_start_time', 'valid_end_time'], name: 'visitor_pass_IDX_VISITOR_PASS_VALIDITY')]
class VisitorPass implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键ID'])]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Visitor::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'visitor_id', referencedColumnName: 'id', nullable: false, unique: true)]
    private ?Visitor $visitor = null;

    #[ORM\Column(name: 'pass_code', type: Types::STRING, length: 50, unique: true, options: ['comment' => '通行码'])]
    #[Assert\NotBlank(message: '通行码不能为空')]
    #[Assert\Length(max: 50, maxMessage: '通行码不能超过50个字符')]
    #[IndexColumn]
    private string $passCode = '';

    #[ORM\Column(name: 'qr_code', type: Types::TEXT, options: ['comment' => '二维码内容'])]
    #[Assert\Length(max: 5000, maxMessage: '二维码内容不能超过5000个字符')]
    private string $qrCode = '';

    #[ORM\Column(name: 'valid_start_time', type: Types::DATETIME_IMMUTABLE, options: ['comment' => '有效开始时间'])]
    #[Assert\NotNull(message: '有效开始时间不能为空')]
    #[Assert\DateTime(message: '有效开始时间格式无效')]
    private ?\DateTimeImmutable $validStartTime = null;

    #[ORM\Column(name: 'valid_end_time', type: Types::DATETIME_IMMUTABLE, options: ['comment' => '有效结束时间'])]
    #[Assert\NotNull(message: '有效结束时间不能为空')]
    #[Assert\DateTime(message: '有效结束时间格式无效')]
    #[Assert\Expression(expression: 'this.getValidEndTime() > this.getValidStartTime()', message: '有效结束时间必须晚于有效开始时间')]
    private ?\DateTimeImmutable $validEndTime = null;

    #[ORM\Column(name: 'use_time', type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '使用时间'])]
    #[Assert\DateTime(message: '使用时间格式无效')]
    private ?\DateTimeImmutable $useTime = null;

    #[ORM\Column(name: 'create_time', type: Types::DATETIME_IMMUTABLE, options: ['comment' => '创建时间'])]
    #[Assert\NotNull(message: '创建时间不能为空')]
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

    public function getPassCode(): string
    {
        return $this->passCode;
    }

    public function setPassCode(string $passCode): void
    {
        $this->passCode = $passCode;
    }

    public function getQrCode(): string
    {
        return $this->qrCode;
    }

    public function setQrCode(string $qrCode): void
    {
        $this->qrCode = $qrCode;
    }

    public function getValidStartTime(): ?\DateTimeImmutable
    {
        return $this->validStartTime;
    }

    public function setValidStartTime(\DateTimeImmutable $validStartTime): void
    {
        $this->validStartTime = $validStartTime;
    }

    public function getValidEndTime(): ?\DateTimeImmutable
    {
        return $this->validEndTime;
    }

    public function setValidEndTime(\DateTimeImmutable $validEndTime): void
    {
        $this->validEndTime = $validEndTime;
    }

    public function getUseTime(): ?\DateTimeImmutable
    {
        return $this->useTime;
    }

    public function setUseTime(?\DateTimeImmutable $useTime): void
    {
        $this->useTime = $useTime;
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
        return sprintf('通行码: %s', $this->passCode);
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * 计算字段：是否已使用
     */
    public function getIsUsed(): bool
    {
        return null !== $this->useTime;
    }
}
