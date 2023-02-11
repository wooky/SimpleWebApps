<?php

declare(strict_types=1);

namespace SimpleWebApps\EventBus;

use function assert;

use Psr\Log\LoggerInterface;
use Socket;

use function strlen;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @SuppressWarnings(PHPMD.ErrorControlOperator)
 */
class EventBus implements EventBusInterface
{
  private const HOST = '127.255.255.255';
  private const PORT = 12345;
  private const MAX_SIZE = 65000;

  private Serializer $serializer;

  public function __construct(
    private LoggerInterface $logger,
  ) {
    $this->serializer = new Serializer([new BackedEnumNormalizer(), new ObjectNormalizer()], [new JsonEncoder()]);
  }

  public function post(Event $event): bool
  {
    $sock = $this->bind();
    if (!$sock) {
      return false;
    }
    $payload = $this->serializer->serialize($event, 'json');
    assert(strlen($payload) <= self::MAX_SIZE);
    $result = @socket_sendto($sock, $payload, strlen($payload), 0, self::HOST, self::PORT);
    $this->logger->debug('Posted event', [
      'payload' => $payload,
      'result' => $result,
    ]);

    return false !== $result;
  }

  public function get(): iterable
  {
    $sock = $this->bind();
    if (!$sock) {
      return;
    }

    $host = '';
    $port = 0;
    $message = null;
    $this->logger->debug('Listening for events');
    while (true) {
      $result = socket_recvfrom($sock, $message, self::MAX_SIZE, 0, $host, $port);
      if (false === $result) {
        $this->logger->error('Unable to receive data from socket');
        continue;
      }
      $this->logger->debug('Received data', [
        'data' => $message,
      ]);
      $event = $this->serializer->deserialize($message, Event::class, 'json');
      if (!$event instanceof Event) {
        $this->logger->error('Received data from socket that is not an Event');
        continue;
      }
      $this->logger->debug('Parsed event', [
        'event' => $event,
      ]);
      yield $event;
    }
  }

  private function bind(): ?Socket
  {
    $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    if (!$sock) {
      $this->logger->error('Failed to create listening socket', [
        'socketLastError' => socket_last_error(),
      ]);

      return null;
    }
    if (!socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1)) {
      $this->logger->error('Failed to set socket reuse option');

      return null;
    }
    if (!socket_set_option($sock, SOL_SOCKET, SO_BROADCAST, 1)) {
      $this->logger->error('Failed to set socket broadcast option');

      return null;
    }
    if (!@socket_bind($sock, self::HOST, self::PORT)) {
      $this->logger->error('Failed to bind socket');

      return null;
    }

    return $sock;
  }
}
