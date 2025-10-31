<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Tourze\VisitorManageBundle\Entity\Visitor;
use Tourze\VisitorManageBundle\Entity\VisitorPass;
use Tourze\VisitorManageBundle\Enum\VisitorAction;
use Tourze\VisitorManageBundle\Enum\VisitorStatus;
use Tourze\VisitorManageBundle\Exception\QrCodeGenerationException;
use Tourze\VisitorManageBundle\Exception\VisitorOperationException;
use Tourze\VisitorManageBundle\Repository\VisitorPassRepository;
use Tourze\VisitorManageBundle\Repository\VisitorRepository;

readonly class VisitorPassService
{
    private const PASS_CODE_LENGTH = 8;
    private const PASS_CODE_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    private const MAX_GENERATE_ATTEMPTS = 10;
    private const DEFAULT_VALID_HOURS = 8;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private VisitorLogService $logService,
        private VisitorPassRepository $repository,
        private VisitorRepository $visitorRepository,
    ) {
    }

    /**
     * 生成访客通行码
     */
    public function generatePass(Visitor $visitor, int $operatorId, int $validHours = self::DEFAULT_VALID_HOURS): VisitorPass
    {
        $this->validateVisitorForPassGeneration($visitor);

        $pass = $this->createPass($visitor, $validHours);
        $this->persistPass($pass);
        $this->logPassGeneration($visitor, $pass->getPassCode(), $operatorId);

        return $pass;
    }

    /**
     * 验证通行码是否有效
     */
    public function validatePass(string $passCode): bool
    {
        $pass = $this->repository->findByPassCode($passCode);

        if (null === $pass) {
            return false;
        }

        return $this->isPassValid($pass);
    }

    /**
     * 使用通行码
     */
    public function usePass(string $passCode, int $operatorId): void
    {
        $pass = $this->repository->findByPassCode($passCode);

        if (null === $pass) {
            throw VisitorOperationException::validationError('通行码不存在');
        }

        // 检查是否已使用
        if (null !== $pass->getUseTime()) {
            throw VisitorOperationException::validationError('通行码已被使用');
        }

        // 检查是否有效
        if (!$this->isPassValid($pass)) {
            throw VisitorOperationException::validationError('通行码无效或已过期');
        }

        // 标记为已使用
        $pass->setUseTime(new \DateTimeImmutable());
        $this->entityManager->flush();

        // 记录日志
        $visitor = $pass->getVisitor();
        if (null !== $visitor) {
            $this->logService->logAction(
                $visitor,
                VisitorAction::PASS_USED,
                $operatorId,
                "通行码使用: {$passCode}"
            );
        }
    }

    /**
     * 检查通行码是否有效
     */
    public function isPassValid(VisitorPass $pass): bool
    {
        $now = new \DateTime();

        // 检查是否已使用
        if (null !== $pass->getUseTime()) {
            return false;
        }

        // 检查是否在有效期内
        if ($pass->getValidStartTime() > $now || $pass->getValidEndTime() < $now) {
            return false;
        }

        return true;
    }

    /**
     * 根据通行码获取通行证
     */
    public function getPassByCode(string $passCode): ?VisitorPass
    {
        return $this->repository->findByPassCode($passCode);
    }

    /**
     * 获取所有有效的通行码
     *
     * @return VisitorPass[]
     */
    public function getValidPasses(): array
    {
        return $this->repository->findValidPasses();
    }

    /**
     * 为已审批访客自动生成通行码
     */
    public function autoGeneratePassForApprovedVisitor(int $visitorId, int $operatorId): VisitorPass
    {
        $visitor = $this->visitorRepository->find($visitorId);

        if (null === $visitor) {
            throw VisitorOperationException::validationError("访客 ID {$visitorId} 不存在");
        }

        return $this->generatePass($visitor, $operatorId);
    }

    /**
     * 验证访客是否可以生成通行码
     */
    private function validateVisitorForPassGeneration(Visitor $visitor): void
    {
        if (VisitorStatus::APPROVED !== $visitor->getStatus()) {
            throw VisitorOperationException::validationError('访客未通过审批，无法生成通行码');
        }
    }

    /**
     * 创建通行码对象
     */
    private function createPass(Visitor $visitor, int $validHours): VisitorPass
    {
        $pass = new VisitorPass();
        $pass->setVisitor($visitor);
        $pass->setPassCode($this->generateUniquePassCode());
        $pass->setQrCode($this->generateQrCode($pass->getPassCode()));

        $now = new \DateTimeImmutable();
        $pass->setValidStartTime($now);
        $pass->setValidEndTime((clone $now)->modify("+{$validHours} hours"));
        $pass->setCreateTime($now);

        return $pass;
    }

    /**
     * 持久化通行码
     */
    private function persistPass(VisitorPass $pass): void
    {
        $this->entityManager->persist($pass);
        $this->entityManager->flush();
    }

    /**
     * 记录通行码生成日志
     */
    private function logPassGeneration(Visitor $visitor, string $passCode, int $operatorId): void
    {
        $this->logService->logAction(
            $visitor,
            VisitorAction::PASS_GENERATED,
            $operatorId,
            "通行码生成成功: {$passCode}"
        );
    }

    /**
     * 生成唯一通行码
     */
    private function generateUniquePassCode(): string
    {
        $attempts = 0;

        do {
            $passCode = $this->generatePassCode();
            $existing = $this->repository->findByPassCode($passCode);

            if (null === $existing) {
                return $passCode;
            }

            ++$attempts;
        } while ($attempts < self::MAX_GENERATE_ATTEMPTS);

        throw VisitorOperationException::operationFailed('无法生成唯一通行码');
    }

    /**
     * 生成通行码
     */
    private function generatePassCode(): string
    {
        $code = '';
        $charsLength = strlen(self::PASS_CODE_CHARS);

        for ($i = 0; $i < self::PASS_CODE_LENGTH; ++$i) {
            $code .= self::PASS_CODE_CHARS[random_int(0, $charsLength - 1)];
        }

        return $code;
    }

    /**
     * 生成二维码数据
     */
    private function generateQrCode(string $passCode): string
    {
        // 简化的二维码数据生成
        // 在实际应用中可以集成二维码生成库
        $jsonData = json_encode([
            'type' => 'visitor_pass',
            'code' => $passCode,
            'generated_at' => (new \DateTime())->format('Y-m-d H:i:s'),
        ]);

        if (false === $jsonData) {
            throw new QrCodeGenerationException('Failed to encode JSON data for QR code');
        }

        return base64_encode($jsonData);
    }
}
