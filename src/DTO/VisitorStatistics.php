<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\DTO;

class VisitorStatistics
{
    public function __construct(
        private int $totalCount,
        private int $pendingCount,
        private int $approvedCount,
        private int $rejectedCount,
        private int $cancelledCount = 0,
    ) {
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    public function getPendingCount(): int
    {
        return $this->pendingCount;
    }

    public function getApprovedCount(): int
    {
        return $this->approvedCount;
    }

    public function getRejectedCount(): int
    {
        return $this->rejectedCount;
    }

    public function getCancelledCount(): int
    {
        return $this->cancelledCount;
    }

    public function getApprovalRate(): float
    {
        if (0 === $this->totalCount) {
            return 0.0;
        }

        return round(($this->approvedCount / $this->totalCount) * 100, 2);
    }

    public function getRejectionRate(): float
    {
        if (0 === $this->totalCount) {
            return 0.0;
        }

        return round(($this->rejectedCount / $this->totalCount) * 100, 2);
    }
}
