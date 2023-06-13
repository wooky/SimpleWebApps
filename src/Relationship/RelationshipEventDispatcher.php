<?php

declare(strict_types=1);

namespace SimpleWebApps\Relationship;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use SimpleWebApps\Common\EventQueueAndDispatcher;
use SimpleWebApps\Entity\Relationship;
use SimpleWebApps\Relationship\Event\RelationshipActivatedEvent;
use SimpleWebApps\Relationship\Event\RelationshipRemovedEvent;

#[AsEntityListener(event: Events::preUpdate, method: 'preRelationshipUpdate', entity: Relationship::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'onRelationshipUpdate', entity: Relationship::class)]
#[AsEntityListener(event: Events::preRemove, method: 'preRelationshipRemove', entity: Relationship::class)]
#[AsEntityListener(event: Events::postRemove, method: 'onRelationshipRemove', entity: Relationship::class)]
/**
 * TODO https://github.com/doctrine/orm/issues/2326 hopes and prayers that only one relationship gets removed at a time.
 */
final class RelationshipEventDispatcher
{
  use EventQueueAndDispatcher;

  public function preRelationshipUpdate(Relationship $relationship, PreUpdateEventArgs $event): void
  {
    if ($event->hasChangedField('active') && true === $event->getNewValue('active')) {
      $this->queueEvent((string) $relationship->getId(), new RelationshipActivatedEvent($relationship));
    }
  }

  public function onRelationshipUpdate(Relationship $relationship): void
  {
    $this->maybeDispatchEvent((string) $relationship->getId());
  }

  public function preRelationshipRemove(Relationship $relationship): void
  {
    $this->queueEvent('__DELETED__', new RelationshipRemovedEvent($relationship, $relationship->getId()));
  }

  public function onRelationshipRemove(): void
  {
    $this->dispatchEvent('__DELETED__');
  }
}
