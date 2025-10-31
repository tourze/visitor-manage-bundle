<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Service;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Tourze\VisitorManageBundle\DTO\VisitorRegistrationData;
use Tourze\VisitorManageBundle\Entity\Visitor;
use Tourze\VisitorManageBundle\Enum\VisitorAction;
use Tourze\VisitorManageBundle\Enum\VisitorStatus;
use Tourze\VisitorManageBundle\Exception\VisitorOperationException;

readonly class VisitorRegistrationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private VisitorValidationService $validationService,
        private VisitorLogService $logService,
    ) {
    }

    public function registerVisitor(VisitorRegistrationData $data, int $operatorId): Visitor
    {
        $this->validationService->validateRegistrationData($data);

        $visitor = $this->createVisitorFromData($data);

        return $this->executeInTransaction(function () use ($visitor, $operatorId) {
            $this->entityManager->persist($visitor);
            $this->entityManager->flush();

            $this->logVisitorAction($visitor, VisitorAction::REGISTERED, $operatorId, '访客注册成功');

            return $visitor;
        });
    }

    public function updateVisitor(Visitor $visitor, VisitorRegistrationData $data, int $operatorId): Visitor
    {
        $this->validationService->validateRegistrationData($data);

        return $this->executeInTransaction(function () use ($visitor, $data, $operatorId) {
            $this->populateVisitorFromData($visitor, $data);
            $visitor->setUpdateTime(new \DateTimeImmutable());

            $this->entityManager->flush();

            $this->logVisitorAction($visitor, VisitorAction::REGISTERED, $operatorId, '访客信息更新');

            return $visitor;
        });
    }

    public function cancelVisitor(Visitor $visitor, int $operatorId, ?string $reason = null): void
    {
        $this->executeInTransaction(function () use ($visitor, $operatorId, $reason) {
            $visitor->setStatus(VisitorStatus::CANCELLED);
            $visitor->setUpdateTime(new \DateTimeImmutable());

            $this->entityManager->flush();

            $remark = null !== $reason ? "访客取消: {$reason}" : '访客取消';
            $this->logVisitorAction($visitor, VisitorAction::CANCELLED, $operatorId, $remark);
        });
    }

    /**
     * @param array<int, VisitorRegistrationData> $dataList
     * @return array<int, Visitor>
     */
    public function bulkRegisterVisitors(array $dataList, int $operatorId): array
    {
        if (0 === count($dataList)) {
            return [];
        }

        // 预验证所有数据，避免部分成功的情况
        $this->validateBulkData($dataList);

        return $this->executeInTransaction(function () use ($dataList, $operatorId) {
            $visitors = [];
            foreach ($dataList as $data) {
                $visitor = $this->createVisitorFromData($data);
                $this->entityManager->persist($visitor);
                $visitors[] = $visitor;
            }

            $this->entityManager->flush();

            $this->logBulkActions($visitors, VisitorAction::REGISTERED, $operatorId, '批量注册访客');

            return $visitors;
        });
    }

    /**
     * 在事务中执行操作，提供统一的错误处理和回滚机制
     */
    /**
     * @template T
     * @param callable(): T $operation
     * @return T
     */
    private function executeInTransaction(callable $operation): mixed
    {
        $this->entityManager->beginTransaction();
        try {
            $result = $operation();
            $this->entityManager->commit();

            return $result;
        } catch (\Exception $exception) {
            $this->entityManager->rollback();

            // 记录错误日志（如果可能的话）
            if ($exception instanceof Exception) {
                // 数据库相关异常
                throw VisitorOperationException::databaseError($exception->getMessage(), $exception);
            }

            // 重新抛出原异常
            throw $exception;
        }
    }

    /**
     * 创建新的访客实体并设置基础信息
     */
    private function createVisitorFromData(VisitorRegistrationData $data): Visitor
    {
        $visitor = new Visitor();
        $this->populateVisitorFromData($visitor, $data);
        $visitor->setStatus(VisitorStatus::PENDING);

        $now = new \DateTimeImmutable();
        $visitor->setCreateTime($now);
        $visitor->setUpdateTime($now);

        return $visitor;
    }

    /**
     * 批量验证数据，提前发现所有错误
     * @param array<int, VisitorRegistrationData> $dataList
     */
    private function validateBulkData(array $dataList): void
    {
        foreach ($dataList as $index => $data) {
            try {
                $this->validationService->validateRegistrationData($data);
            } catch (\InvalidArgumentException $exception) {
                throw VisitorOperationException::validationError('第 ' . ($index + 1) . " 条数据验证失败: {$exception->getMessage()}", $exception);
            }
        }
    }

    /**
     * 记录访客操作日志，带错误处理
     */
    private function logVisitorAction(
        Visitor $visitor,
        VisitorAction $action,
        int $operatorId,
        string $remark,
    ): void {
        try {
            $this->logService->logAction($visitor, $action, $operatorId, $remark);
        } catch (\Exception $exception) {
            // 日志记录失败不应该影响主要业务流程
            // 这里可以记录到系统日志或通过其他方式处理
            error_log("访客日志记录失败: {$exception->getMessage()}");
        }
    }

    /**
     * 批量记录操作日志
     * @param array<int, Visitor> $visitors
     */
    private function logBulkActions(
        array $visitors,
        VisitorAction $action,
        int $operatorId,
        string $remark,
    ): void {
        try {
            $this->logService->batchLogActions($visitors, $action, $operatorId, $remark);
        } catch (\Exception $exception) {
            // 批量日志失败时，尝试逐个记录
            foreach ($visitors as $visitor) {
                $this->logVisitorAction($visitor, $action, $operatorId, $remark);
            }
        }
    }

    private function populateVisitorFromData(Visitor $visitor, VisitorRegistrationData $data): void
    {
        if (null !== $data->name) {
            $visitor->setName($data->name);
        }
        if (null !== $data->mobile) {
            $visitor->setMobile($data->mobile);
        }
        if (null !== $data->company) {
            $visitor->setCompany($data->company);
        }
        if (null !== $data->reason) {
            $visitor->setReason($data->reason);
        }
        if (null !== $data->appointmentTime) {
            $visitor->setAppointmentTime(\DateTimeImmutable::createFromMutable($data->appointmentTime));
        }
        $visitor->setVehicleNumber($data->vehicleNumber);
        $visitor->setContactPerson($data->contactPerson);
        $visitor->setIdCard($data->idCard);
    }
}
