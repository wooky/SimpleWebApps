<?php

declare(strict_types=1);

namespace SimpleWebApps\WeightTracker;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use SimpleWebApps\Auth\AuthenticatedUser;
use SimpleWebApps\Entity\WeightRecord;
use SimpleWebApps\EventBus\Event;
use SimpleWebApps\EventBus\EventStreamInitialPayloadListener;
use SimpleWebApps\Relationship\Event\RelationshipActivatedEvent;
use SimpleWebApps\Relationship\Event\RelationshipRemovedEvent;
use SimpleWebApps\User\Event\UsernameChangedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Uid\Ulid;

#[AsEntityListener(event: Events::postPersist, method: 'onWeightRecordCreated', entity: WeightRecord::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'onWeightRecordUpdated', entity: WeightRecord::class)]
#[AsEntityListener(event: Events::postRemove, method: 'onWeightRecordDeleted', entity: WeightRecord::class)]
#[AsEntityListener(event: Events::preRemove, method: 'onWeightRecordPreRemoved', entity: WeightRecord::class)]
// skip relationship create as it'll initially be in the pending state.
#[AsEventListener(event: RelationshipActivatedEvent::class, method: 'onRelationshipActivated')]
#[AsEventListener(event: RelationshipRemovedEvent::class, method: 'onRelationshipRemoved')]
#[AsEventListener(event: UsernameChangedEvent::class, method: 'onUsernameChanged')]
final class WeightTrackerListener implements EventStreamInitialPayloadListener
{
  /**
   * TODO https://github.com/doctrine/orm/issues/2326 hacky af.
   */
  private ?Ulid $lastWeightRecordIdRemoved = null;

  public function __construct(
    private readonly WeightRecordBroadcaster $broadcaster,
  ) {
    // Do nothing.
  }

  public function initiallyConnected(AuthenticatedUser $authenticatedUser, array $topics): ?Event
  {
    return $this->broadcaster->createInitialPayloadEvent($authenticatedUser, $topics);
  }

  public function onWeightRecordCreated(WeightRecord $weightRecord): void
  {
    $this->broadcaster->onWeightRecordCreated($weightRecord);
  }

  public function onWeightRecordUpdated(WeightRecord $weightRecord): void
  {
    $this->broadcaster->onWeightRecordUpdated($weightRecord);
  }

  public function onWeightRecordPreRemoved(WeightRecord $weightRecord): void
  {
    $this->lastWeightRecordIdRemoved = $weightRecord->getId();
  }

  public function onWeightRecordDeleted(WeightRecord $weightRecord): void
  {
    if ($this->lastWeightRecordIdRemoved) {
      $this->broadcaster->onWeightRecordDeleted($weightRecord, $this->lastWeightRecordIdRemoved);
      $this->lastWeightRecordIdRemoved = null;
    }
  }

  public function onRelationshipActivated(RelationshipActivatedEvent $event): void
  {
    $this->broadcaster->onRelationshipActivated($event->relationship);
  }

  public function onRelationshipRemoved(RelationshipRemovedEvent $event): void
  {
    $this->broadcaster->onRelationshipRemoved($event->relationship);
  }

  public function onUsernameChanged(UsernameChangedEvent $event): void
  {
    $this->broadcaster->onUsernameChanged($event->user);
  }
}
