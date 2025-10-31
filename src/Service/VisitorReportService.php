<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Service;

use Tourze\VisitorManageBundle\DTO\VisitorStatistics;
use Tourze\VisitorManageBundle\Entity\Visitor;
use Tourze\VisitorManageBundle\Enum\VisitorStatus;
use Tourze\VisitorManageBundle\Repository\VisitorRepository;

readonly class VisitorReportService
{
    public function __construct(
        private VisitorRepository $visitorRepository,
    ) {
    }

    /**
     * 获取访客统计数据
     */
    public function getVisitorStatistics(\DateTime $startDate, \DateTime $endDate): VisitorStatistics
    {
        $totalCount = $this->visitorRepository->countByDateRangeAndStatus($startDate, $endDate, null);
        $pendingCount = $this->visitorRepository->countByDateRangeAndStatus($startDate, $endDate, VisitorStatus::PENDING);
        $approvedCount = $this->visitorRepository->countByDateRangeAndStatus($startDate, $endDate, VisitorStatus::APPROVED);
        $rejectedCount = $this->visitorRepository->countByDateRangeAndStatus($startDate, $endDate, VisitorStatus::REJECTED);

        return new VisitorStatistics(
            $totalCount,
            $pendingCount,
            $approvedCount,
            $rejectedCount
        );
    }

    /**
     * 获取日访客报表
     *
     * @return array<array{name: string, mobile: string, company: string, status: string, appointmentTime: string}>
     */
    public function getDailyVisitorReport(\DateTime $date): array
    {
        $visitors = $this->visitorRepository->findByDate($date);
        $report = [];

        foreach ($visitors as $visitor) {
            $report[] = [
                'name' => $visitor->getName(),
                'mobile' => $visitor->getMobile(),
                'company' => $visitor->getCompany(),
                'status' => $visitor->getStatus()->value,
                'appointmentTime' => $visitor->getAppointmentTime()?->format('H:i') ?? '',
            ];
        }

        return $report;
    }

    /**
     * 按状态获取访客
     *
     * @return array<int, Visitor>
     */
    public function getVisitorsByStatus(VisitorStatus $status): array
    {
        return $this->visitorRepository->findByStatus($status);
    }

    /**
     * 生成Excel报表数据
     *
     * @return array{headers: array<string>, rows: array<array<mixed>>}
     */
    public function generateExcelReport(\DateTime $startDate, \DateTime $endDate): array
    {
        $visitors = $this->visitorRepository->findByDateRange($startDate, $endDate);

        $headers = [
            '访客姓名',
            '手机号码',
            '公司名称',
            '来访原因',
            '预约时间',
            '状态',
            '创建时间',
        ];

        $rows = [];
        foreach ($visitors as $visitor) {
            $rows[] = [
                $visitor->getName(),
                $visitor->getMobile(),
                $visitor->getCompany(),
                $visitor->getReason(),
                $visitor->getAppointmentTime()?->format('Y-m-d H:i') ?? '',
                $this->getStatusLabel($visitor->getStatus()),
                $visitor->getCreateTime()?->format('Y-m-d H:i') ?? '',
            ];
        }

        return [
            'headers' => $headers,
            'rows' => $rows,
        ];
    }

    /**
     * 获取来访最多的公司
     *
     * @return array<array{company: string, count: int}>
     */
    public function getTopCompanies(\DateTime $startDate, \DateTime $endDate, int $limit = 10): array
    {
        return $this->visitorRepository->getTopCompaniesByVisitorCount($startDate, $endDate, $limit);
    }

    /**
     * 获取访客趋势数据
     *
     * @return array<array{date: string, count: int}>
     */
    public function getVisitorTrendData(\DateTime $startDate, \DateTime $endDate): array
    {
        return $this->visitorRepository->getVisitorCountByDateRange($startDate, $endDate);
    }

    /**
     * 获取状态标签
     */
    private function getStatusLabel(VisitorStatus $status): string
    {
        return match ($status) {
            VisitorStatus::PENDING => '待审批',
            VisitorStatus::APPROVED => '已通过',
            VisitorStatus::REJECTED => '已拒绝',
            VisitorStatus::CANCELLED => '已取消',
            VisitorStatus::SIGNED_IN => '已签到',
            default => '未知状态',
        };
    }
}
