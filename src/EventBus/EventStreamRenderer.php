<?php

declare(strict_types=1);

namespace SimpleWebApps\EventBus;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

use function in_array;

class EventStreamRenderer
{
  /**
   * @param iterable<EventStreamInitialPayloadListener> $initialPayloadListeners
   */
  public function __construct(
    private EventBusInterface $eventBus,
    #[TaggedIterator('SimpleWebApps.EventStreamInitialPayloadListener')] private iterable $initialPayloadListeners,
    private LoggerInterface $logger,
  ) {
    // Do nothing.
  }

  /**
   * @param string[] $topics
   */
  public function createResponse(string $userId, array $topics): Response
  {
    session_write_close();
    $response = new StreamedResponse(fn () => $this->waitLoop($userId, $topics));
    $response->headers->set('Content-Type', 'text/event-stream');
    $response->headers->set('Cache-Control', 'no-cache');
    $response->headers->set('X-Accel-Buffering', 'no');

    return $response;
  }

  /**
   * @param string[] $topics
   */
  private function waitLoop(string $userId, array $topics): void
  {
    self::flush();
    foreach ($this->initialPayloadListeners as $listener) {
      $event = $listener->initiallyConnected($userId, $topics);
      if ($event) {
        self::writePayload($event);
      }
    }
    foreach ($this->eventBus->get() as $event) {
      $shouldWrite = match ($event->scope) {
        EventScope::SpecifiedTopic => in_array($userId, $event->users, true) && in_array($event->topic, $topics, true),
        EventScope::AllTopics => in_array($userId, $event->users, true),
        EventScope::AllUsersOfSpecifiedTopic => in_array($event->topic, $topics, true),
      };
      if ($shouldWrite) {
        self::writePayload($event);
      }
    }
  }

  private static function writePayload(Event $event): void
  {
    $payload = str_replace("\n", '&#10;', $event->payload);
    echo 'event: '.$event->sseEvent."\n";
    echo 'data: '.$payload."\n\n";
    self::flush();
  }

  private static function flush(): void
  {
    if (ob_get_level()) {
      ob_flush();
    }
    flush();
  }
}
