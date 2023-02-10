<?php

declare(strict_types=1);

namespace SimpleWebApps\User\Event;

use SimpleWebApps\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

final class UsernameChangedEvent extends Event
{
  public function __construct(
    public User $user,
  ) {
    // Do nothing.
  }
}
