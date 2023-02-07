<?php

declare(strict_types=1);

namespace SimpleWebApps\EventBus;

interface EventBusInterface
{
  public function post(Event $event): bool;

  /**
   * @return iterable<string>
   */
  public function get(string $userId): iterable;
}
