<?php

declare(strict_types=1);

namespace SimpleWebApps\Relationship\Event;

use SimpleWebApps\Entity\Relationship;
use Symfony\Component\Uid\Ulid;
use Symfony\Contracts\EventDispatcher\Event;

final class RelationshipRemovedEvent extends Event
{
  public function __construct(
    public Relationship $relationship,
    public Ulid $id,
  ) {
    // Do nothing.
  }
}
