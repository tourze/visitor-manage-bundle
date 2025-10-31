<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Event;

class VisitorRegisteredEvent extends AbstractDomainEvent
{
    /**
     * @param array<string, mixed> $visitorData
     */
    public function __construct(
        private int $visitorId,
        private array $visitorData,
        private int $operatorId,
    ) {
        parent::__construct();
    }

    public function getVisitorId(): int
    {
        return $this->visitorId;
    }

    /**
     * @return array<string, mixed>
     */
    public function getVisitorData(): array
    {
        return $this->visitorData;
    }

    public function getOperatorId(): int
    {
        return $this->operatorId;
    }

    public function getEventName(): string
    {
        return 'visitor.registered';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'visitorId' => $this->visitorId,
            'visitorData' => $this->visitorData,
            'operatorId' => $this->operatorId,
            'occurredOn' => $this->getOccurredOn()->format(\DateTimeInterface::ATOM),
        ];
    }
}
