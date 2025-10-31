<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Event;

abstract class AbstractDomainEvent implements DomainEventInterface
{
    private \DateTimeImmutable $occurredOn;

    public function __construct()
    {
        $this->occurredOn = new \DateTimeImmutable();
    }

    public function getOccurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }

    public function getVersion(): string
    {
        return '1.0';
    }

    abstract public function getEventName(): string;

    abstract public function toArray(): array;
}
