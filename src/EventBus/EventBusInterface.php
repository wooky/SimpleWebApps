<?php

declare(strict_types=1);

namespace SimpleWebApps\EventBus;

interface EventBusInterface
{
  public function post(Event $event): bool;

  /**
   * @return iterable<Event>
   */
  public function get(): iterable;
}
