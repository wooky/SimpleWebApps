<?php

declare(strict_types=1);

namespace SimpleWebApps\EventBus;

interface EventStreamInitialPayloadListener
{
  /**
   * @param string[] $topics
   */
  public function initiallyConnected(string $userId, array $topics): ?Event;
}
