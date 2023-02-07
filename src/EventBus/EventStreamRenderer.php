<?php

declare(strict_types=1);

namespace SimpleWebApps\EventBus;

use function in_array;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EventStreamRenderer
{
  public function __construct(
    private EventBusInterface $eventBus,
  ) {
    // Do nothing.
  }

  /**
   * @param string[] $topics
   * @param string[] $initialPayload
   */
  public function createResponse(string $userId, array $topics, array $initialPayload): Response
  {
    session_write_close();
    $response = new StreamedResponse(fn () => $this->waitLoop($userId, $topics, $initialPayload));
    $response->headers->set('Content-Type', 'text/event-stream');
    $response->headers->set('Cache-Control', 'no-cache');
    $response->headers->set('X-Accel-Buffering', 'no');

    return $response;
  }

  /**
   * @param string[] $topics
   * @param string[] $initialPayload
   */
  private function waitLoop(string $userId, array $topics, array $initialPayload): void
  {
    foreach ($initialPayload as $payload) {
      self::writePayload($payload);
    }
    foreach ($this->eventBus->get() as $event) {
      if (in_array($userId, $event->users, true) && in_array($event->topic, $topics, true)) {
        self::writePayload($event->payload);
      }
    }
  }

  private static function writePayload(string $payload): void
  {
    echo 'data: '.$payload."\n\n";
    @ob_flush();
    flush();
  }
}
