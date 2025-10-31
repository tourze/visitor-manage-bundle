<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\EventListener;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tourze\VisitorManageBundle\Event\VisitorApprovedEvent;
use Tourze\VisitorManageBundle\Event\VisitorRegisteredEvent;
use Tourze\VisitorManageBundle\Service\VisitorLogService;
use Tourze\VisitorManageBundle\Service\VisitorPassService;

#[Autoconfigure(public: true)]
class VisitorEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private VisitorLogService $logService,
        private VisitorPassService $passService,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            VisitorRegisteredEvent::class => 'onVisitorRegistered',
            VisitorApprovedEvent::class => 'onVisitorApproved',
        ];
    }

    /**
     * 处理访客注册事件
     */
    public function onVisitorRegistered(VisitorRegisteredEvent $event): void
    {
        try {
            $visitorData = $event->getVisitorData();
            $visitorName = $visitorData['name'] ?? 'Unknown';
            assert(is_string($visitorName), 'Visitor name should be a string');
            $nameString = $visitorName;

            $this->logService->logEventAction(
                $event->getVisitorId(),
                $event->getEventName(),
                $event->getOperatorId(),
                '访客注册事件 - 访客: ' . $nameString
            );
        } catch (\Exception $exception) {
            $this->logService->logError(
                null,
                '事件处理异常: ' . $exception->getMessage(),
                $event->getOperatorId(),
                [
                    'event' => $event->getEventName(),
                    'visitorId' => $event->getVisitorId(),
                    'error' => $exception->getMessage(),
                ]
            );
        }
    }

    /**
     * 处理访客审批通过事件
     */
    public function onVisitorApproved(VisitorApprovedEvent $event): void
    {
        try {
            // 记录审批事件日志
            $this->logService->logEventAction(
                $event->getVisitorId(),
                $event->getEventName(),
                $event->getApproverId(),
                "访客审批通过事件 - 审批人: {$event->getApproverName()}, 备注: {$event->getRemark()}"
            );

            // 自动为已审批访客生成通行码
            $this->passService->autoGeneratePassForApprovedVisitor(
                $event->getVisitorId(),
                $event->getApproverId()
            );
        } catch (\Exception $exception) {
            $this->logService->logError(
                null,
                '自动生成通行码失败: ' . $exception->getMessage(),
                $event->getApproverId(),
                [
                    'event' => $event->getEventName(),
                    'visitorId' => $event->getVisitorId(),
                    'approverId' => $event->getApproverId(),
                    'error' => $exception->getMessage(),
                ]
            );
        }
    }

    /**
     * 批量处理事件
     *
     * @param VisitorRegisteredEvent[] $events
     */
    public function processBatchEvents(array $events): void
    {
        foreach ($events as $event) {
            $this->onVisitorRegistered($event);
        }
    }
}
