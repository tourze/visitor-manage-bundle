<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\VisitorManageBundle\DTO\VisitorStatistics;
use Tourze\VisitorManageBundle\Enum\VisitorStatus;
use Tourze\VisitorManageBundle\Service\VisitorReportService;

/**
 * @internal
 */
#[CoversClass(VisitorReportService::class)]
#[RunTestsInSeparateProcesses]
class VisitorReportServiceTest extends AbstractIntegrationTestCase
{
    private VisitorReportService $service;

    protected function onSetUp(): void
    {
        $this->service = self::getService(VisitorReportService::class);
    }

    public function testGetVisitorStatistics(): void
    {
        $startDate = new \DateTime('-7 days');
        $endDate = new \DateTime();

        $statistics = $this->service->getVisitorStatistics($startDate, $endDate);

        $this->assertInstanceOf(VisitorStatistics::class, $statistics);
    }

    public function testGetDailyVisitorReport(): void
    {
        $date = new \DateTime();

        $report = $this->service->getDailyVisitorReport($date);

        $this->assertIsArray($report, 'getDailyVisitorReport should return an array');
    }

    public function testGetVisitorsByStatus(): void
    {
        $status = VisitorStatus::PENDING;

        $result = $this->service->getVisitorsByStatus($status);

        $this->assertIsArray($result, 'getVisitorsByStatus should return an array');
    }

    public function testGenerateExcelReport(): void
    {
        $startDate = new \DateTime('-7 days');
        $endDate = new \DateTime();

        $excelData = $this->service->generateExcelReport($startDate, $endDate);

        $this->assertArrayHasKey('headers', $excelData);
        $this->assertArrayHasKey('rows', $excelData);
    }

    public function testGetTopCompanies(): void
    {
        $startDate = new \DateTime('-30 days');
        $endDate = new \DateTime();
        $limit = 10;

        $result = $this->service->getTopCompanies($startDate, $endDate, $limit);

        $this->assertLessThanOrEqual($limit, count($result));
    }

    public function testGetVisitorTrendData(): void
    {
        $startDate = new \DateTime('-7 days');
        $endDate = new \DateTime();

        $result = $this->service->getVisitorTrendData($startDate, $endDate);

        $this->assertIsArray($result, 'getVisitorTrendData should return an array');
    }
}
