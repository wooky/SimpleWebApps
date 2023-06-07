<?php

declare(strict_types=1);

namespace SimpleWebApps\EventBus;

use SimpleWebApps\Common\TurboStreamRenderer;

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
    public string $sseEvent = TurboStreamRenderer::MESSAGE,
  ) {
    // Do nothing.
  }
}
