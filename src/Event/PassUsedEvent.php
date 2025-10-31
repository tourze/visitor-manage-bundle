<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Event;

class PassUsedEvent extends AbstractDomainEvent
{
    public function __construct(
        private int $passId,
        private string $passCode,
        private int $visitorId,
        private int $operatorId,
    ) {
        parent::__construct();
    }

    public function getPassId(): int
    {
        return $this->passId;
    }

    public function getPassCode(): string
    {
        return $this->passCode;
    }

    public function getVisitorId(): int
    {
        return $this->visitorId;
    }

    public function getOperatorId(): int
    {
        return $this->operatorId;
    }

    public function getEventName(): string
    {
        return 'visitor.pass.used';
    }

    public function toArray(): array
    {
        return [
            'passId' => $this->passId,
            'passCode' => $this->passCode,
            'visitorId' => $this->visitorId,
            'operatorId' => $this->operatorId,
            'occurredOn' => $this->getOccurredOn()->format(\DateTimeInterface::ATOM),
        ];
    }
}
