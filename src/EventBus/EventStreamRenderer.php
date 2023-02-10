<?php

declare(strict_types=1);

namespace SimpleWebApps\EventBus;

use function in_array;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EventStreamRenderer
{
  /**
   * @param iterable<EventStreamInitialPayloadListener> $initialPayloadListeners
   */
  public function __construct(
    private EventBusInterface $eventBus,
    #[TaggedIterator('SimpleWebApps.EventStreamInitialPayloadListener')] private iterable $initialPayloadListeners,
    LoggerInterface $logger,
  ) {
    $logger->debug('yes', ['listeners' => $initialPayloadListeners]);
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
    @ob_flush();
    flush();
    foreach ($this->initialPayloadListeners as $listener) {
      if ($event = $listener->initiallyConnected($userId, $topics)) {
        self::writePayload($event);
      }
    }
    foreach ($this->eventBus->get() as $event) {
      if (in_array($userId, $event->users, true) && in_array($event->topic, $topics, true)) {
        self::writePayload($event);
      }
    }
  }

  private static function writePayload(Event $event): void
  {
    echo 'event: '.$event->topic."\n";
    echo 'data: '.$event->payload."\n\n";
    @ob_flush();
    flush();
  }
}
