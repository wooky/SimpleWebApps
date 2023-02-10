<?php

declare(strict_types=1);

namespace SimpleWebApps\Relationship\Event;

use SimpleWebApps\Entity\Relationship;
use Symfony\Contracts\EventDispatcher\Event;

final class RelationshipActivatedEvent extends Event
{
  public function __construct(
    public Relationship $relationship,
  ) {
    // Do nothing.
  }
}
