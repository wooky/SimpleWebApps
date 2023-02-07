<?php
namespace SimpleWebApps\EventBus;

interface EventBusInterface
{
  function post(Event $event): bool;

  /**
   * @return iterable<string>
   */
  function get(string $userId): iterable;
}
