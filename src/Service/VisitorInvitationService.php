<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Tourze\VisitorManageBundle\Entity\Visitor;
use Tourze\VisitorManageBundle\Entity\VisitorInvitation;
use Tourze\VisitorManageBundle\Enum\InvitationStatus;
use Tourze\VisitorManageBundle\Enum\VisitorAction;
use Tourze\VisitorManageBundle\Exception\InvalidInviterException;
use Tourze\VisitorManageBundle\Exception\InvitationExpiredException;
use Tourze\VisitorManageBundle\Repository\VisitorInvitationRepository;

readonly class VisitorInvitationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private VisitorInvitationRepository $visitorInvitationRepository,
        private VisitorLogService $logService,
    ) {
    }

    /**
     * 创建访客邀请
     */
    public function createInvitation(Visitor $visitor, ?object $inviter, int $expireHours = 24): VisitorInvitation
    {
        if (null === $inviter) {
            throw new InvalidInviterException('邀请者不能为空');
        }

        $inviterId = $this->extractInviterId($inviter);

        $invitation = new VisitorInvitation();
        $invitation->setVisitor($visitor);
        $invitation->setInviter($inviterId);
        $invitation->setStatus(InvitationStatus::PENDING);
        $invitation->setCreateTime(new \DateTimeImmutable());

        // 设置过期时间
        $expireAt = (new \DateTimeImmutable())->modify("+{$expireHours} hours");
        $invitation->setExpireTime($expireAt);

        // 生成唯一邀请码
        $invitation->setInviteCode($this->generateUniqueInviteCode());

        $this->entityManager->persist($invitation);
        $this->entityManager->flush();

        // 记录日志
        $this->logService->logAction(
            $visitor,
            VisitorAction::REGISTERED, // 使用REGISTERED表示邀请创建
            $inviterId,
            "邀请创建，邀请码: {$invitation->getInviteCode()}"
        );

        return $invitation;
    }

    /**
     * 确认邀请
     */
    public function confirmInvitation(VisitorInvitation $invitation, int $operatorId): void
    {
        $this->validateInvitationCanBeProcessed($invitation);

        $invitation->setStatus(InvitationStatus::CONFIRMED);
        $this->entityManager->flush();

        $visitor = $invitation->getVisitor();
        if (null !== $visitor) {
            $this->logService->logAction(
                $visitor,
                VisitorAction::APPROVED,
                $operatorId,
                "邀请确认，邀请码: {$invitation->getInviteCode()}"
            );
        }
    }

    /**
     * 拒绝邀请
     */
    public function rejectInvitation(VisitorInvitation $invitation, int $operatorId): void
    {
        $this->validateInvitationCanBeProcessed($invitation);

        $invitation->setStatus(InvitationStatus::REJECTED);
        $this->entityManager->flush();

        $visitor = $invitation->getVisitor();
        if (null !== $visitor) {
            $this->logService->logAction(
                $visitor,
                VisitorAction::REJECTED,
                $operatorId,
                "邀请拒绝，邀请码: {$invitation->getInviteCode()}"
            );
        }
    }

    /**
     * 取消邀请（由邀请者主动取消）
     */
    public function cancelInvitation(VisitorInvitation $invitation, int $operatorId): void
    {
        $invitation->setStatus(InvitationStatus::REJECTED);
        $this->entityManager->flush();

        $visitor = $invitation->getVisitor();
        if (null !== $visitor) {
            $this->logService->logAction(
                $visitor,
                VisitorAction::CANCELLED,
                $operatorId,
                "邀请取消，邀请码: {$invitation->getInviteCode()}"
            );
        }
    }

    /**
     * 根据邀请码获取邀请
     */
    public function getInvitation(string $inviteCode): ?VisitorInvitation
    {
        return $this->visitorInvitationRepository->findByInviteCode($inviteCode);
    }

    /**
     * 处理过期邀请
     */
    public function expireInvitations(int $operatorId): int
    {
        $expiredInvitations = $this->visitorInvitationRepository->findExpiredInvitations();

        $count = 0;
        foreach ($expiredInvitations as $invitation) {
            $invitation->setStatus(InvitationStatus::EXPIRED);

            $visitor = $invitation->getVisitor();
            if (null !== $visitor) {
                $this->logService->logAction(
                    $visitor,
                    VisitorAction::ERROR,
                    $operatorId,
                    "邀请过期，邀请码: {$invitation->getInviteCode()}"
                );
            }

            ++$count;
        }

        if ($count > 0) {
            $this->entityManager->flush();
        }

        return $count;
    }

    /**
     * 获取指定邀请者的所有邀请
     *
     * @return VisitorInvitation[]
     */
    public function getInvitationsByInviter(int $inviterId): array
    {
        return $this->visitorInvitationRepository->findByInviter($inviterId);
    }

    /**
     * 生成唯一邀请码
     */
    private function generateUniqueInviteCode(): string
    {
        $maxAttempts = 10;
        $attempts = 0;

        do {
            $inviteCode = $this->generateInviteCode();
            $existing = $this->visitorInvitationRepository->findByInviteCode($inviteCode);

            if (null === $existing) {
                return $inviteCode;
            }

            ++$attempts;
        } while ($attempts < $maxAttempts);

        throw new InvalidInviterException('无法生成唯一邀请码');
    }

    /**
     * 生成邀请码
     */
    private function generateInviteCode(): string
    {
        // 生成8位随机字符串：字母+数字
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code = '';

        for ($i = 0; $i < 8; ++$i) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $code;
    }

    /**
     * 验证邀请是否可以被处理
     */
    private function validateInvitationCanBeProcessed(VisitorInvitation $invitation): void
    {
        // 检查是否已过期
        if ($invitation->getExpireTime() < new \DateTime()) {
            throw new InvitationExpiredException('邀请已过期');
        }

        // 检查是否已经被处理
        if (InvitationStatus::PENDING !== $invitation->getStatus()) {
            throw new InvalidInviterException('邀请已被处理');
        }
    }

    /**
     * 从邀请者对象中提取ID
     *
     * @param object $inviter 邀请者对象，支持多种类型：
     *                        - 有 getId() 方法的对象
     *                        - 有 getIdentifier() 方法的对象
     *                        - 有 id 属性的对象
     *                        - 有 identifier 属性的对象
     */
    private function extractInviterId(object $inviter): int
    {
        if (method_exists($inviter, 'getId')) {
            return (int) $inviter->getId();
        }

        if (method_exists($inviter, 'getIdentifier')) {
            return (int) $inviter->getIdentifier();
        }

        if (property_exists($inviter, 'id') && isset($inviter->id)) {
            return (int) $inviter->id;
        }

        if (property_exists($inviter, 'identifier') && isset($inviter->identifier)) {
            return (int) $inviter->identifier;
        }

        throw new \InvalidArgumentException('无法从邀请者对象中提取ID');
    }
}
