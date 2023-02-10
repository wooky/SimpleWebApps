<?php

declare(strict_types=1);

namespace SimpleWebApps\User;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use SimpleWebApps\Common\EventQueueAndDispatcher;
use SimpleWebApps\Entity\User;
use SimpleWebApps\User\Event\UsernameChangedEvent;

#[AsEntityListener(event: Events::preUpdate, method: 'preUserUpdate', entity: User::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'onUserUpdate', entity: User::class)]
final class UserEventDispatcher
{
  use EventQueueAndDispatcher;

  public function preUserUpdate(User $user, PreUpdateEventArgs $event): void
  {
    if ($event->hasChangedField('username')) {
      $this->queueEvent((string) $user->getId(), new UsernameChangedEvent($user));
    }
  }

  public function onUserUpdate(User $user): void
  {
    $this->maybeDispatchEvent((string) $user->getId());
  }
}
