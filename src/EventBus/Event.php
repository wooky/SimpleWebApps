<?php

declare(strict_types=1);

namespace SimpleWebApps\EventBus;

readonly class Event
{
  /**
   * @param string[] $users
   */
  public function __construct(
    public array $users,
    public string $payload,
  ) {
    // Do nothing.
  }
}
