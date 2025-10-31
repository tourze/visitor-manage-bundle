<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Event;

interface DomainEventInterface
{
    /**
     * 获取事件发生的时间
     */
    public function getOccurredOn(): \DateTimeImmutable;

    /**
     * 获取事件名称
     */
    public function getEventName(): string;

    /**
     * 获取事件版本
     */
    public function getVersion(): string;

    /**
     * 转换为数组
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
