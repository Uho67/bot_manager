<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Common\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final class ExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => ['onKernelException', 0]];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();
        $statusCode = $throwable instanceof HttpExceptionInterface
            ? $throwable->getStatusCode()
            : 500;

        if ($statusCode < 500) {
            return;
        }

        $request = $event->getRequest();

        $this->logger->error('Unhandled server exception', [
            'status_code' => $statusCode,
            'method'      => $request->getMethod(),
            'url'         => $request->getUri(),
            'exception'   => $throwable->getMessage(),
            'class'       => $throwable::class,
            'file'        => $throwable->getFile(),
            'line'        => $throwable->getLine(),
            'trace'       => $throwable->getTraceAsString(),
        ]);
    }
}
