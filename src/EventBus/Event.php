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
    public string $topic,
    public string $payload,
    public EventScope $scope = EventScope::SpecifiedTopic,
  ) {
    // Do nothing.
  }
}