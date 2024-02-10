<?php

declare(strict_types=1);

namespace SimpleWebApps\EventBus;

use SimpleWebApps\Auth\AuthenticatedUser;

interface EventStreamInitialPayloadListener
{
  /**
   * @param string[] $topics
   */
  public function initiallyConnected(AuthenticatedUser $authenticatedUser, array $topics): ?Event;
}
