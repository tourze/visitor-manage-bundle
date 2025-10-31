<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Tests\DTO;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\VisitorManageBundle\DTO\VisitorStatistics;

/**
 * @internal
 */
#[CoversClass(VisitorStatistics::class)]
class VisitorStatisticsTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $stats = new VisitorStatistics(100, 20, 60, 10, 10);

        $this->assertSame(100, $stats->getTotalCount());
        $this->assertSame(20, $stats->getPendingCount());
        $this->assertSame(60, $stats->getApprovedCount());
        $this->assertSame(10, $stats->getRejectedCount());
        $this->assertSame(10, $stats->getCancelledCount());
    }

    public function testGetApprovalRate(): void
    {
        $stats = new VisitorStatistics(100, 20, 60, 10, 10);

        $this->assertSame(60.0, $stats->getApprovalRate());
    }

    public function testGetApprovalRateWithZeroTotal(): void
    {
        $stats = new VisitorStatistics(0, 0, 0, 0, 0);

        $this->assertSame(0.0, $stats->getApprovalRate());
    }

    public function testGetRejectionRate(): void
    {
        $stats = new VisitorStatistics(100, 20, 60, 10, 10);

        $this->assertSame(10.0, $stats->getRejectionRate());
    }

    public function testGetRejectionRateWithZeroTotal(): void
    {
        $stats = new VisitorStatistics(0, 0, 0, 0, 0);

        $this->assertSame(0.0, $stats->getRejectionRate());
    }

    public function testDefaultCancelledCount(): void
    {
        $stats = new VisitorStatistics(100, 20, 60, 10);

        $this->assertSame(0, $stats->getCancelledCount());
    }
}
