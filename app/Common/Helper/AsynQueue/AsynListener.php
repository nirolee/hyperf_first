<?php
declare(strict_types=1);

namespace App\Common\Helper\AsynQueue;

use Hyperf\AsyncQueue\Event\AfterHandle;
use Hyperf\AsyncQueue\Event\BeforeHandle;
use Hyperf\AsyncQueue\Event\FailedHandle;
use Hyperf\AsyncQueue\Event\QueueLength;
use Hyperf\AsyncQueue\Event\RetryHandle;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;

/**
 * Class AsynListener
 * @package App\Common\Helper\AsynQueue
 * @Listener()
 */
class AsynListener implements ListenerInterface
{

    /**
     * @return string[] returns the events that you want to listen
     */
    public function listen(): array
    {
        return [
            BeforeHandle::class, AfterHandle::class, FailedHandle::class, RetryHandle::class, QueueLength::class
        ];
    }

    /**
     * Handle the Event when the event is triggered, all listeners will
     * complete before the event is returned to the EventDispatcher.
     */
    public function process(object $event)
    {
        if ($event instanceof BeforeHandle) {
        } elseif ($event instanceof AfterHandle) {
        } elseif ($event instanceof FailedHandle) {
        } elseif ($event instanceof RetryHandle) {
        } elseif ($event instanceof QueueLength) {
        }
    }
}