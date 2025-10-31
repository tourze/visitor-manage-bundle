<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Event;

class VisitorApprovedEvent extends AbstractDomainEvent
{
    public function __construct(
        private int $visitorId,
        private int $approverId,
        private string $approverName,
        private string $remark,
    ) {
        parent::__construct();
    }

    public function getVisitorId(): int
    {
        return $this->visitorId;
    }

    public function getApproverId(): int
    {
        return $this->approverId;
    }

    public function getApproverName(): string
    {
        return $this->approverName;
    }

    public function getRemark(): string
    {
        return $this->remark;
    }

    public function getEventName(): string
    {
        return 'visitor.approved';
    }

    public function toArray(): array
    {
        return [
            'visitorId' => $this->visitorId,
            'approverId' => $this->approverId,
            'approverName' => $this->approverName,
            'remark' => $this->remark,
            'occurredOn' => $this->getOccurredOn()->format(\DateTimeInterface::ATOM),
        ];
    }
}
