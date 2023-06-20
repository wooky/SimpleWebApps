<?php

declare(strict_types=1);

namespace SimpleWebApps\Common;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\Event;

use function array_key_exists;
use function assert;

trait EventQueueAndDispatcher
{
  /** @var array<string, Event> */ private array $eventQueue = [];

  public function __construct(
    private readonly EventDispatcherInterface $eventDispatcher,
    private readonly LoggerInterface $logger,
  ) {
    // Do nothing.
  }

  protected function queueEvent(string $id, Event $event): void
  {
    $this->logger->debug('Queuing event', ['id' => $id]);
    assert(!array_key_exists($id, $this->eventQueue));
    $this->eventQueue[$id] = $event;
    $this->logger->debug('Queued event', ['id' => $id]);
  }

  protected function dispatchEvent(string $id): void
  {
    $this->logger->debug('Dispatching event', ['id' => $id]);
    assert(array_key_exists($id, $this->eventQueue));
    $this->eventDispatcher->dispatch($this->eventQueue[$id]);
    unset($this->eventQueue[$id]);
    $this->logger->debug('Dispatched event', ['id' => $id]);
  }

  protected function maybeDispatchEvent(string $id): void
  {
    if (array_key_exists($id, $this->eventQueue)) {
      $this->dispatchEvent($id);
    }
  }
}
