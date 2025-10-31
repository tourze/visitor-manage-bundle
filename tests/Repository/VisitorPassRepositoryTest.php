<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\VisitorManageBundle\Entity\Visitor;
use Tourze\VisitorManageBundle\Entity\VisitorPass;
use Tourze\VisitorManageBundle\Enum\VisitorStatus;
use Tourze\VisitorManageBundle\Repository\VisitorPassRepository;

/**
 * @internal
 */
#[CoversClass(VisitorPassRepository::class)]
#[RunTestsInSeparateProcesses]
final class VisitorPassRepositoryTest extends AbstractRepositoryTestCase
{
    private VisitorPassRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(VisitorPassRepository::class);
    }

    protected function getRepository(): VisitorPassRepository
    {
        return $this->repository;
    }

    protected function createNewEntity(): object
    {
        return $this->createVisitorPass([], false);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function createVisitor(array $attributes = [], bool $persist = true): Visitor
    {
        /** @var int $counter */
        static $counter = 0;
        $visitor = new Visitor();

        $uniqueValue = (int) (microtime(true) * 1000000) + (++$counter);

        $attributes = array_merge([
            'name' => '测试访客' . $uniqueValue,
            'mobile' => '1380' . str_pad((string) ($uniqueValue % 10000000), 7, '0', STR_PAD_LEFT),
            'company' => '测试公司' . $uniqueValue,
            'reason' => '测试来访' . $uniqueValue,
            'appointmentTime' => new \DateTimeImmutable('+1 day'),
            'status' => VisitorStatus::PENDING,
        ], $attributes);

        /** @var string $name */
        $name = $attributes['name'];
        /** @var string $mobile */
        $mobile = $attributes['mobile'];
        /** @var string $company */
        $company = $attributes['company'];
        /** @var string $reason */
        $reason = $attributes['reason'];
        /** @var \DateTimeImmutable $appointmentTime */
        $appointmentTime = $attributes['appointmentTime'];
        /** @var VisitorStatus $status */
        $status = $attributes['status'];

        $visitor->setName($name);
        $visitor->setMobile($mobile);
        $visitor->setCompany($company);
        $visitor->setReason($reason);
        $visitor->setAppointmentTime($appointmentTime);
        $visitor->setStatus($status);

        if ($persist) {
            $this->persistAndFlush($visitor);
        }

        return $visitor;
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function createVisitorPass(array $attributes = [], bool $persist = true): VisitorPass
    {
        /** @var int $counter */
        static $counter = 0;
        $pass = new VisitorPass();

        $uniqueValue = (int) (microtime(true) * 1000000) + (++$counter);

        // 当不持久化时，创建一个独立的visitor但不保存
        $visitor = $attributes['visitor'] ?? $this->createVisitor([], false);
        $this->assertInstanceOf(Visitor::class, $visitor);

        $attributes = array_merge([
            'visitor' => $visitor,
            'passCode' => 'VP' . $uniqueValue,
            'validStartTime' => new \DateTimeImmutable(),
            'validEndTime' => new \DateTimeImmutable('+8 hours'),
            'createTime' => new \DateTimeImmutable(),
        ], $attributes);

        /** @var Visitor $visitorEntity */
        $visitorEntity = $attributes['visitor'];
        /** @var string $passCode */
        $passCode = $attributes['passCode'];
        /** @var \DateTimeImmutable $validStartTime */
        $validStartTime = $attributes['validStartTime'];
        /** @var \DateTimeImmutable $validEndTime */
        $validEndTime = $attributes['validEndTime'];
        /** @var \DateTimeImmutable $createTime */
        $createTime = $attributes['createTime'];

        $pass->setVisitor($visitorEntity);
        $pass->setPassCode($passCode);
        $pass->setValidStartTime($validStartTime);
        $pass->setValidEndTime($validEndTime);
        $pass->setCreateTime($createTime);

        if (isset($attributes['useTime'])) {
            /** @var \DateTimeImmutable $useTime */
            $useTime = $attributes['useTime'];
            $pass->setUseTime($useTime);
        }

        if (isset($attributes['qrCode'])) {
            /** @var string $qrCode */
            $qrCode = $attributes['qrCode'];
            $pass->setQrCode($qrCode);
        }

        if ($persist) {
            // 确保visitor先被持久化
            self::getEntityManager()->persist($visitorEntity);
            $this->persistAndFlush($pass);
        }

        return $pass;
    }

    public function testFindByPassCode(): void
    {
        $pass = $this->createVisitorPass(['passCode' => 'PASS123']);

        $result = $this->repository->findByPassCode('PASS123');

        $this->assertInstanceOf(VisitorPass::class, $result);
        $this->assertSame('PASS123', $result->getPassCode());
    }

    public function testFindUsedPasses(): void
    {
        $this->createVisitorPass(['useTime' => new \DateTimeImmutable('-1 hour')]);
        $this->createVisitorPass(['useTime' => new \DateTimeImmutable('-2 hours')]);
        $this->createVisitorPass();

        $result = $this->repository->findUsedPasses();

        $this->assertGreaterThanOrEqual(2, count($result));
        foreach ($result as $pass) {
            $this->assertInstanceOf(VisitorPass::class, $pass);
            $this->assertNotNull($pass->getUseTime());
        }
    }

    public function testFindValidPasses(): void
    {
        $now = new \DateTimeImmutable();

        $this->createVisitorPass([
            'validStartTime' => $now->modify('-1 hour'),
            'validEndTime' => $now->modify('+1 hour'),
        ]);
        $this->createVisitorPass([
            'validStartTime' => $now->modify('-1 hour'),
            'validEndTime' => $now->modify('+1 hour'),
            'useTime' => $now,
        ]);
        $this->createVisitorPass([
            'validStartTime' => $now->modify('+1 hour'),
            'validEndTime' => $now->modify('+2 hours'),
        ]);

        $result = $this->repository->findValidPasses();

        $this->assertGreaterThanOrEqual(1, count($result));
        if (count($result) > 0) {
            $this->assertInstanceOf(VisitorPass::class, $result[0]);
            $this->assertNull($result[0]->getUseTime());
        }
    }
}
