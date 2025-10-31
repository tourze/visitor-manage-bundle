<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Tourze\VisitorManageBundle\Entity\Visitor;
use Tourze\VisitorManageBundle\Entity\VisitorLog;
use Tourze\VisitorManageBundle\Enum\VisitorAction;
use Tourze\VisitorManageBundle\Exception\VisitorNotFoundException;
use Tourze\VisitorManageBundle\Repository\VisitorLogRepository;
use Tourze\VisitorManageBundle\Repository\VisitorRepository;

readonly class VisitorLogService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private VisitorLogRepository $visitorLogRepository,
        private VisitorRepository $visitorRepository,
    ) {
    }

    public function logAction(
        Visitor $visitor,
        VisitorAction $action,
        int $operatorId,
        ?string $remark = null,
    ): VisitorLog {
        $log = new VisitorLog();
        $log->setVisitor($visitor);
        $log->setAction($action);
        $log->setOperator($operatorId);
        $log->setRemark($remark ?? '');
        $log->setCreateTime(new \DateTimeImmutable());

        $this->entityManager->persist($log);
        $this->entityManager->flush();

        return $log;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function logError(
        ?Visitor $visitor,
        string $errorMessage,
        int $operatorId,
        array $context = [],
    ): VisitorLog {
        $remark = $this->buildErrorRemark($errorMessage, $context);

        $log = new VisitorLog();
        $log->setVisitor($visitor);
        $log->setAction(VisitorAction::ERROR);
        $log->setOperator($operatorId);
        $log->setRemark($remark);
        $log->setCreateTime(new \DateTimeImmutable());

        $this->entityManager->persist($log);
        $this->entityManager->flush();

        return $log;
    }

    /**
     * @param array<int, Visitor> $visitors
     * @return array<int, VisitorLog>
     */
    public function batchLogActions(
        array $visitors,
        VisitorAction $action,
        int $operatorId,
        ?string $remark = null,
    ): array {
        if (0 === count($visitors)) {
            return [];
        }

        $logs = [];
        foreach ($visitors as $visitor) {
            $log = new VisitorLog();
            $log->setVisitor($visitor);
            $log->setAction($action);
            $log->setOperator($operatorId);
            $log->setRemark($remark ?? '');
            $log->setCreateTime(new \DateTimeImmutable());

            $this->entityManager->persist($log);
            $logs[] = $log;
        }

        $this->entityManager->flush();

        return $logs;
    }

    /**
     * @return array<VisitorLog>
     */
    public function getVisitorLogs(Visitor $visitor): array
    {
        return $this->visitorLogRepository->findByVisitor($visitor);
    }

    /**
     * @return array<VisitorLog>
     */
    public function getLogsByDateRange(\DateTime $startDate, \DateTime $endDate): array
    {
        return $this->visitorLogRepository->findByDateRange($startDate, $endDate);
    }

    /**
     * @return array<VisitorLog>
     */
    public function getLogsByAction(VisitorAction $action): array
    {
        return $this->visitorLogRepository->findByAction($action);
    }

    /**
     * @return array<VisitorLog>
     */
    public function getLogsByOperator(int $operatorId): array
    {
        return $this->visitorLogRepository->findByOperator($operatorId);
    }

    public function countLogsByAction(VisitorAction $action): int
    {
        return $this->visitorLogRepository->countByAction($action);
    }

    /**
     * 记录事件操作日志
     */
    public function logEventAction(int $visitorId, string $eventName, int $operatorId, string $remark): VisitorLog
    {
        $visitor = $this->visitorRepository->find($visitorId);

        if (null === $visitor) {
            throw new VisitorNotFoundException($visitorId);
        }

        $action = $this->mapEventNameToAction($eventName);

        return $this->logAction($visitor, $action, $operatorId, $remark);
    }

    /**
     * 将事件名称映射到操作类型
     */
    private function mapEventNameToAction(string $eventName): VisitorAction
    {
        return match ($eventName) {
            'visitor.registered' => VisitorAction::REGISTERED,
            'visitor.approved' => VisitorAction::APPROVED,
            'visitor.pass.used' => VisitorAction::PASS_USED,
            default => VisitorAction::OTHER,
        };
    }

    /**
     * @param array<string, mixed> $context
     */
    private function buildErrorRemark(string $errorMessage, array $context): string
    {
        $remark = "错误: {$errorMessage}";

        if (count($context) > 0) {
            $contextStr = json_encode($context, JSON_UNESCAPED_UNICODE);
            $remark .= " | 上下文: {$contextStr}";
        }

        return $remark;
    }
}
