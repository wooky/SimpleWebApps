<?php

declare(strict_types=1);

namespace SimpleWebApps\WeightTracker;

use function assert;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

use function in_array;

use InvalidArgumentException;
use SimpleWebApps\Auth\RelationshipCapability;
use SimpleWebApps\Entity\Relationship;
use SimpleWebApps\Entity\User;
use SimpleWebApps\Entity\WeightRecord;
use SimpleWebApps\EventBus\Event;
use SimpleWebApps\EventBus\EventBusInterface;
use SimpleWebApps\EventBus\EventStreamInitialPayloadListener;
use SimpleWebApps\Repository\UserRepository;
use SimpleWebApps\Repository\WeightRecordRepository;
use Symfony\Component\Uid\Ulid;

#[AsEntityListener(event: Events::postPersist, method: 'onWeightRecordCreated', entity: WeightRecord::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'onWeightRecordUpdated', entity: WeightRecord::class)]
#[AsEntityListener(event: Events::postRemove, method: 'onWeightRecordDeleted', entity: WeightRecord::class)]
#[AsEntityListener(event: Events::preRemove, method: 'onWeightRecordPreRemoved', entity: WeightRecord::class)]
// skip relationship create as it'll initially be in the pending state.
// TODO listen on preUpdate so we don't send unnecessary data
#[AsEntityListener(event: Events::postUpdate, method: 'onRelationshipUpdated', entity: Relationship::class)]
#[AsEntityListener(event: Events::postRemove, method: 'onRelationshipDeleted', entity: Relationship::class)]
#[AsEntityListener(event: Events::preRemove, method: 'onRelationshipPreRemoved', entity: Relationship::class)]
/**
 * TODO https://github.com/doctrine/orm/issues/2326 once this gets resolved, add readonly to class.
 */
class WeightRecordBroadcaster implements EventStreamInitialPayloadListener
{
  public const TOPIC = 'weight_tracker';

  /**
   * TODO https://github.com/doctrine/orm/issues/2326 hacky af.
   */
  private ?Ulid $lastIdRemoved = null;

  public function __construct(
    private readonly UserRepository $userRepository,
    private readonly WeightRecordRepository $weightRecordRepository,
    private readonly EventBusInterface $eventBus,
    private readonly WeightRecordCommandRenderer $commandRenderer,
  ) {
    // Do nothing.
  }

  public function initiallyConnected(string $userId, array $topics): ?Event
  {
    if (!in_array(self::TOPIC, $topics, true)) {
      return null;
    }
    $user = $this->userRepository->find($userId);
    assert(null !== $user);
    $controlledUsers = $this->userRepository->getControlledUsersIncludingSelf($user, RelationshipCapability::Read->permissionsRequired());
    $controlledUserIds = array_map(fn (User $user) => $user->getId()?->toBinary() ?? throw new InvalidArgumentException('User has no ID'), $controlledUsers);
    $weightRecords = $this->weightRecordRepository->getDataPoints($controlledUserIds);
    $initialPayload = json_encode($this->commandRenderer->initialData($user, $weightRecords));

    return new Event([], self::TOPIC, $initialPayload);
  }

  /**
   * @SuppressWarnings(PHPMD.UnusedFormalParameter)
   */
  public function onWeightRecordCreated(WeightRecord $weightRecord, LifecycleEventArgs $event): void
  {
    $this->onWeightRecordChange($weightRecord, $this->commandRenderer->weightRecordCreated($weightRecord));
  }

  /**
   * @SuppressWarnings(PHPMD.UnusedFormalParameter)
   */
  public function onWeightRecordUpdated(WeightRecord $weightRecord, LifecycleEventArgs $event): void
  {
    $this->onWeightRecordChange($weightRecord, $this->commandRenderer->weightRecordUpdated($weightRecord));
  }

  public function onWeightRecordPreRemoved(WeightRecord $weightRecord, LifecycleEventArgs $event): void
  {
    $this->lastIdRemoved = $weightRecord->getId();
  }

  /**
   * @SuppressWarnings(PHPMD.UnusedFormalParameter)
   */
  public function onWeightRecordDeleted(WeightRecord $weightRecord, LifecycleEventArgs $event): void
  {
    if ($this->lastIdRemoved) {
      $this->onWeightRecordChange($weightRecord, $this->commandRenderer->weightRecordDeleted($this->lastIdRemoved));
      $this->lastIdRemoved = null;
    }
  }

  private function onWeightRecordChange(WeightRecord $weightRecord, array $payload): void
  {
    $owner = $weightRecord->getOwner();
    assert(null !== $owner);
    $affectedUsers = $this->userRepository->getControllingUsersIncludingSelf($owner, RelationshipCapability::Read->permissionsRequired());
    $this->broadcast($affectedUsers, $payload);
  }

  /**
   * TODO.
   *
   * @SuppressWarnings(PHPMD.UnusedFormalParameter)
   */
  public function onRelationshipUpdated(Relationship $relationship, LifecycleEventArgs $event): void
  {
    $user = $relationship->getToUser();
    assert(null !== $user);
    $userId = $user->getId()?->toBinary();
    assert(null !== $userId);
    $weightRecords = $this->weightRecordRepository->getDataPoints([$userId]);
    $this->onRelationshipChange($relationship, $this->commandRenderer->relationshipUpdated($user, $weightRecords));
  }

  /**
   * @SuppressWarnings(PHPMD.UnusedFormalParameter)
   */
  public function onRelationshipPreRemoved(Relationship $relationship, LifecycleEventArgs $event): void
  {
    $this->lastIdRemoved = $relationship->getToUser()?->getId();
  }

  /**
   * TODO.
   *
   * @SuppressWarnings(PHPMD.UnusedFormalParameter)
   */
  public function onRelationshipDeleted(Relationship $relationship, LifecycleEventArgs $event): void
  {
    if ($this->lastIdRemoved) {
      $this->onRelationshipChange($relationship, $this->commandRenderer->relationshipDeleted($this->lastIdRemoved));
      $this->lastIdRemoved = null;
    }
  }

  private function onRelationshipChange(Relationship $relationship, array $payload): void
  {
    $fromUser = $relationship->getFromUser();
    assert(null !== $fromUser);
    $affectedUsers = [$fromUser];
    $this->broadcast($affectedUsers, $payload);
  }

  /**
   * @param User[] $affectedUsers
   */
  private function broadcast(array $affectedUsers, array $payloadArray): void
  {
    $payload = json_encode($payloadArray);
    foreach ($affectedUsers as $user) {
      $users = [(string) $user->getId()];
      $this->eventBus->post(new Event($users, self::TOPIC, $payload));
    }
  }
}
