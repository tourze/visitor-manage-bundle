<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Tourze\VisitorManageBundle\Entity\Visitor;
use Tourze\VisitorManageBundle\Entity\VisitorLog;
use Tourze\VisitorManageBundle\Enum\VisitorAction;
use Tourze\VisitorManageBundle\Enum\VisitorStatus;
use Tourze\VisitorManageBundle\Exception\InvalidVisitorStatusException;
use Tourze\VisitorManageBundle\Repository\VisitorRepository;

readonly class VisitorApprovalService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private VisitorRepository $visitorRepository,
        private VisitorValidationService $validationService,
        private VisitorLogService $logService,
    ) {
    }

    /**
     * 提交访客审批
     */
    public function submitForApproval(Visitor $visitor, int $operatorId): void
    {
        $visitorId = $visitor->getId();
        if (null === $visitorId) {
            throw new InvalidVisitorStatusException('访客ID不能为空');
        }
        $this->validationService->validateVisitorExists($visitorId);

        // 验证访客状态是否允许提交审批
        if (VisitorStatus::PENDING !== $visitor->getStatus()) {
            throw new InvalidVisitorStatusException('访客状态不允许提交审批');
        }

        $visitor->setUpdateTime(new \DateTimeImmutable());
        $this->entityManager->flush();

        $this->logService->logAction(
            $visitor,
            VisitorAction::REGISTERED,
            $operatorId,
            '访客提交审批'
        );
    }

    /**
     * 审批通过访客
     */
    public function approveVisitor(Visitor $visitor, object $approver, string $remark = ''): void
    {
        $this->validationService->validateApprovalPermission($approver);

        // 验证访客状态
        if (VisitorStatus::PENDING !== $visitor->getStatus()) {
            throw new InvalidVisitorStatusException('访客状态不允许审批');
        }

        $visitor->setStatus(VisitorStatus::APPROVED);
        $visitor->setUpdateTime(new \DateTimeImmutable());

        $this->entityManager->flush();

        $logRemark = '' !== $remark ? "审批通过: {$remark}" : '审批通过';
        // 获取审批者ID，支持多种对象类型
        $approverId = $this->extractApproverId($approver);
        $this->logService->logAction(
            $visitor,
            VisitorAction::APPROVED,
            $approverId,
            $logRemark
        );
    }

    /**
     * 拒绝访客审批
     */
    public function rejectVisitor(Visitor $visitor, object $approver, string $remark = ''): void
    {
        $this->validationService->validateApprovalPermission($approver);

        // 验证访客状态
        if (VisitorStatus::PENDING !== $visitor->getStatus()) {
            throw new InvalidVisitorStatusException('访客状态不允许审批');
        }

        $visitor->setStatus(VisitorStatus::REJECTED);
        $visitor->setUpdateTime(new \DateTimeImmutable());

        $this->entityManager->flush();

        $logRemark = '' !== $remark ? "审批拒绝: {$remark}" : '审批拒绝';
        $approverId = $this->extractApproverId($approver);
        $this->logService->logAction(
            $visitor,
            VisitorAction::REJECTED,
            $approverId,
            $logRemark
        );
    }

    /**
     * 获取待审批的访客列表
     *
     * @return Visitor[]
     */
    public function getPendingApprovals(): array
    {
        return $this->visitorRepository->findByStatus(VisitorStatus::PENDING);
    }

    /**
     * 批量审批通过访客
     *
     * @param Visitor[] $visitors
     */
    public function batchApproveVisitors(array $visitors, object $approver, string $remark = ''): void
    {
        if (0 === count($visitors)) {
            return;
        }

        $this->validationService->validateApprovalPermission($approver);

        $approvedVisitors = [];
        foreach ($visitors as $visitor) {
            // 只处理待审批状态的访客
            if (VisitorStatus::PENDING === $visitor->getStatus()) {
                $visitor->setStatus(VisitorStatus::APPROVED);
                $visitor->setUpdateTime(new \DateTimeImmutable());
                $approvedVisitors[] = $visitor;
            }
        }

        if (count($approvedVisitors) > 0) {
            $this->entityManager->flush();

            $logRemark = '' !== $remark ? "批量审批通过: {$remark}" : '批量审批通过';
            $approverId = $this->extractApproverId($approver);
            $this->logService->batchLogActions(
                $approvedVisitors,
                VisitorAction::BULK_APPROVED,
                $approverId,
                $logRemark
            );
        }
    }

    /**
     * 批量拒绝访客审批
     *
     * @param Visitor[] $visitors
     */
    public function batchRejectVisitors(array $visitors, object $approver, string $remark = ''): void
    {
        if (0 === count($visitors)) {
            return;
        }

        $this->validationService->validateApprovalPermission($approver);

        $rejectedVisitors = [];
        foreach ($visitors as $visitor) {
            // 只处理待审批状态的访客
            if (VisitorStatus::PENDING === $visitor->getStatus()) {
                $visitor->setStatus(VisitorStatus::REJECTED);
                $visitor->setUpdateTime(new \DateTimeImmutable());
                $rejectedVisitors[] = $visitor;
            }
        }

        if (count($rejectedVisitors) > 0) {
            $this->entityManager->flush();

            $logRemark = '' !== $remark ? "批量审批拒绝: {$remark}" : '批量审批拒绝';
            $approverId = $this->extractApproverId($approver);
            $this->logService->batchLogActions(
                $rejectedVisitors,
                VisitorAction::BULK_REJECTED,
                $approverId,
                $logRemark
            );
        }
    }

    /**
     * 获取访客审批历史记录
     * @return array<VisitorLog>
     */
    public function getApprovalHistory(Visitor $visitor): array
    {
        return $this->logService->getVisitorLogs($visitor);
    }

    /**
     * 从审批者对象中提取ID
     *
     * @param object $approver 审批者对象，支持多种类型：
     *                         - 有 getId() 方法的对象
     *                         - 有 getIdentifier() 方法的对象
     *                         - 有 id 属性的对象
     *                         - 有 identifier 属性的对象
     */
    private function extractApproverId(object $approver): int
    {
        if (method_exists($approver, 'getId')) {
            return (int) $approver->getId();
        }

        if (method_exists($approver, 'getIdentifier')) {
            return (int) $approver->getIdentifier();
        }

        if (property_exists($approver, 'id') && isset($approver->id)) {
            return (int) $approver->id;
        }

        if (property_exists($approver, 'identifier') && isset($approver->identifier)) {
            return (int) $approver->identifier;
        }

        throw new \InvalidArgumentException('无法从审批者对象中提取ID');
    }
}
