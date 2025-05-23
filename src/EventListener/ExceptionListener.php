<?php

declare(strict_types=1);

namespace Jenky\Bundle\ApiError\EventListener;

use Jenky\ApiError\Handler\Symfony\ResponseHandler;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ExceptionListener
{
    public function __construct(
        private readonly ResponseHandler $handler,
    ) {
    }

    public function __invoke(ExceptionEvent $event): void
    {
        if ($response = $this->handler->render($event->getThrowable(), $event->getRequest())) {
            $event->setResponse($response);
        }
    }
}
