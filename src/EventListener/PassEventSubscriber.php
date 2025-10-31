<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\EventListener;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tourze\VisitorManageBundle\Event\PassUsedEvent;
use Tourze\VisitorManageBundle\Service\VisitorLogService;

#[Autoconfigure(public: true)]
class PassEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private VisitorLogService $logService,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PassUsedEvent::class => 'onPassUsed',
        ];
    }

    /**
     * 处理通行码使用事件
     */
    public function onPassUsed(PassUsedEvent $event): void
    {
        try {
            $this->logService->logEventAction(
                $event->getVisitorId(),
                $event->getEventName(),
                $event->getOperatorId(),
                "通行码使用事件 - 通行码: {$event->getPassCode()}, 通行码ID: {$event->getPassId()}"
            );
        } catch (\Exception $exception) {
            $this->logService->logError(
                null,
                '通行码事件处理异常: ' . $exception->getMessage(),
                $event->getOperatorId(),
                [
                    'event' => $event->getEventName(),
                    'passId' => $event->getPassId(),
                    'passCode' => $event->getPassCode(),
                    'visitorId' => $event->getVisitorId(),
                    'error' => $exception->getMessage(),
                ]
            );
        }
    }

    /**
     * 异步处理通行码使用事件
     */
    public function handleAsyncPassUsed(PassUsedEvent $event): bool
    {
        try {
            $this->logService->logEventAction(
                $event->getVisitorId(),
                $event->getEventName(),
                $event->getOperatorId(),
                "异步处理通行码使用 - {$event->getPassCode()}"
            );

            return true;
        } catch (\Exception $exception) {
            $this->logService->logError(
                null,
                '异步通行码处理失败: ' . $exception->getMessage(),
                $event->getOperatorId(),
                ['event' => $event->getEventName(), 'error' => $exception->getMessage()]
            );

            return false;
        }
    }
}
