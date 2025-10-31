<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\VisitorManageBundle\DTO\VisitorRegistrationData;
use Tourze\VisitorManageBundle\DTO\VisitorSearchCriteria;
use Tourze\VisitorManageBundle\Enum\VisitorStatus;
use Tourze\VisitorManageBundle\Exception\VisitorNotFoundException;
use Tourze\VisitorManageBundle\Service\VisitorService;

/**
 * @internal
 */
#[CoversClass(VisitorService::class)]
#[RunTestsInSeparateProcesses]
class VisitorServiceTest extends AbstractIntegrationTestCase
{
    private VisitorService $service;

    protected function onSetUp(): void
    {
        $this->service = self::getService(VisitorService::class);
    }

    public function testRegisterVisitor(): void
    {
        $data = new VisitorRegistrationData();
        $data->name = '测试访客';
        $data->mobile = '13800138000';
        $data->company = '测试公司';
        $data->reason = '商务洽谈';
        $data->appointmentTime = new \DateTime('+1 day');

        $visitor = $this->service->registerVisitor($data);

        $this->assertEquals('测试访客', $visitor->getName());
        $this->assertEquals(VisitorStatus::PENDING, $visitor->getStatus());
    }

    public function testGetVisitorByIdNotFound(): void
    {
        $this->expectException(VisitorNotFoundException::class);

        $this->service->getVisitorById(99999);
    }

    public function testSearchVisitors(): void
    {
        $criteria = new VisitorSearchCriteria();

        $results = $this->service->searchVisitors($criteria);

        $this->assertIsArray($results, 'searchVisitors should return an array');
    }

    public function testGetPendingVisitors(): void
    {
        $visitors = $this->service->getPendingVisitors();

        $this->assertIsArray($visitors, 'getPendingVisitors should return an array');
    }

    public function testCountByStatus(): void
    {
        $count = $this->service->countByStatus(VisitorStatus::PENDING);

        $this->assertGreaterThanOrEqual(0, $count);
    }

    public function testApproveVisitor(): void
    {
        // 创建并持久化一个访客
        $data = new VisitorRegistrationData();
        $data->name = '待审批访客';
        $data->mobile = '13800138001';
        $data->company = '测试公司';
        $data->reason = '商务洽谈';
        $data->appointmentTime = new \DateTime('+1 day');

        $visitor = $this->service->registerVisitor($data);
        $approverId = 123;

        $visitorId = $visitor->getId();
        $this->assertNotNull($visitorId);

        $this->service->approveVisitor($visitorId, $approverId);

        $approvedVisitor = $this->service->getVisitorById($visitorId);
        $this->assertEquals(VisitorStatus::APPROVED, $approvedVisitor->getStatus());
    }

    public function testRejectVisitor(): void
    {
        // 创建并持久化一个访客
        $data = new VisitorRegistrationData();
        $data->name = '待拒绝访客';
        $data->mobile = '13800138002';
        $data->company = '测试公司';
        $data->reason = '商务洽谈';
        $data->appointmentTime = new \DateTime('+1 day');

        $visitor = $this->service->registerVisitor($data);
        $approverId = 456;
        $reason = '不符合来访条件';

        $visitorId = $visitor->getId();
        $this->assertNotNull($visitorId);

        $this->service->rejectVisitor($visitorId, $approverId, $reason);

        $rejectedVisitor = $this->service->getVisitorById($visitorId);
        $this->assertEquals(VisitorStatus::REJECTED, $rejectedVisitor->getStatus());
    }

    public function testSignInVisitor(): void
    {
        // 创建、持久化并审批一个访客
        $data = new VisitorRegistrationData();
        $data->name = '待签入访客';
        $data->mobile = '13800138003';
        $data->company = '测试公司';
        $data->reason = '商务洽谈';
        $data->appointmentTime = new \DateTime('+1 day');

        $visitor = $this->service->registerVisitor($data);
        $visitorId = $visitor->getId();
        $this->assertNotNull($visitorId);

        $this->service->approveVisitor($visitorId, 123);
        $this->service->signInVisitor($visitorId);

        $signedInVisitor = $this->service->getVisitorById($visitorId);
        $this->assertEquals(VisitorStatus::SIGNED_IN, $signedInVisitor->getStatus());
        $this->assertNotNull($signedInVisitor->getSignInTime());
    }

    public function testSignOutVisitor(): void
    {
        // 创建、持久化、审批并签入一个访客
        $data = new VisitorRegistrationData();
        $data->name = '待签出访客';
        $data->mobile = '13800138004';
        $data->company = '测试公司';
        $data->reason = '商务洽谈';
        $data->appointmentTime = new \DateTime('+1 day');

        $visitor = $this->service->registerVisitor($data);
        $visitorId = $visitor->getId();
        $this->assertNotNull($visitorId);

        $this->service->approveVisitor($visitorId, 123);
        $this->service->signInVisitor($visitorId);
        $this->service->signOutVisitor($visitorId);

        $signedOutVisitor = $this->service->getVisitorById($visitorId);
        $this->assertEquals(VisitorStatus::SIGNED_OUT, $signedOutVisitor->getStatus());
        $this->assertNotNull($signedOutVisitor->getSignOutTime());
    }

    public function testApproveVisitorNotFound(): void
    {
        $this->expectException(VisitorNotFoundException::class);

        $this->service->approveVisitor(99999, 123);
    }

    public function testRejectVisitorNotFound(): void
    {
        $this->expectException(VisitorNotFoundException::class);

        $this->service->rejectVisitor(99999, 456, '不存在');
    }

    public function testSignInVisitorNotFound(): void
    {
        $this->expectException(VisitorNotFoundException::class);

        $this->service->signInVisitor(99999);
    }

    public function testSignOutVisitorNotFound(): void
    {
        $this->expectException(VisitorNotFoundException::class);

        $this->service->signOutVisitor(99999);
    }
}
