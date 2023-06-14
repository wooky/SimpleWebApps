<?php

declare(strict_types=1);

namespace SimpleWebApps\WeightTracker;

use SimpleWebApps\Auth\RelationshipCapability;
use SimpleWebApps\Entity\Relationship;
use SimpleWebApps\Entity\User;
use SimpleWebApps\Entity\WeightRecord;
use SimpleWebApps\EventBus\Event;
use SimpleWebApps\EventBus\EventBusInterface;
use SimpleWebApps\Repository\UserRepository;
use SimpleWebApps\Repository\WeightRecordRepository;
use Symfony\Component\Uid\Ulid;

use function assert;
use function in_array;

/**
 * TODO https://github.com/doctrine/orm/issues/2326 once this gets resolved, add readonly to class.
 */
class WeightRecordBroadcaster
{
  public const TOPIC = 'weight_tracker';

  public function __construct(
    private readonly UserRepository $userRepository,
    private readonly WeightRecordRepository $weightRecordRepository,
    private readonly EventBusInterface $eventBus,
    private readonly WeightRecordCommandRenderer $commandRenderer,
  ) {
    // Do nothing.
  }

  public function createInitialPayloadEvent(string $userId, array $topics): ?Event
  {
    if (!in_array(self::TOPIC, $topics, true)) {
      return null;
    }
    $user = $this->userRepository->find($userId);
    assert(null !== $user);
    $controlledUsers = $this->userRepository->getControlledUsersIncludingSelf(
      [$user],
      RelationshipCapability::Read->permissionsRequired(),
    );
    $controlledUserIds = array_map(fn (User $user) => $user->getId()->toBinary(), $controlledUsers);
    $weightRecords = $this->weightRecordRepository->getDataPoints($controlledUserIds);
    $initialPayload = json_encode($this->commandRenderer->initialData($user, $weightRecords));
    assert(false !== $initialPayload);

    return new Event([], self::TOPIC, $initialPayload, sseEvent: self::TOPIC);
  }

  public function onWeightRecordCreated(WeightRecord $weightRecord): void
  {
    $this->onWeightRecordChange($weightRecord, $this->commandRenderer->weightRecordCreated($weightRecord));
  }

  public function onWeightRecordUpdated(WeightRecord $weightRecord): void
  {
    $this->onWeightRecordChange($weightRecord, $this->commandRenderer->weightRecordUpdated($weightRecord));
  }

  public function onWeightRecordDeleted(WeightRecord $weightRecord, Ulid $weightRecordId): void
  {
    $this->onWeightRecordChange($weightRecord, $this->commandRenderer->weightRecordDeleted($weightRecordId));
  }

  private function onWeightRecordChange(WeightRecord $weightRecord, array $payload): void
  {
    $owner = $weightRecord->getOwner();
    assert(null !== $owner);
    $affectedUsers = $this->userRepository->getControllingUsersIncludingSelf(
      [$owner],
      RelationshipCapability::Read->permissionsRequired(),
    );
    $this->broadcast($affectedUsers, $payload);
  }

  public function onRelationshipActivated(Relationship $relationship): void
  {
    $user = $relationship->getToUser();
    assert(null !== $user);
    $userId = $user->getId()->toBinary();
    $weightRecords = $this->weightRecordRepository->getDataPoints([$userId]);
    $this->onRelationshipChange($relationship, $this->commandRenderer->relationshipActivated($user, $weightRecords));
  }

  public function onRelationshipRemoved(Relationship $relationship): void
  {
    $userId = $relationship->getToUser()?->getId();
    assert(null !== $userId);
    $this->onRelationshipChange($relationship, $this->commandRenderer->relationshipDeleted($userId));
  }

  private function onRelationshipChange(Relationship $relationship, array $payload): void
  {
    $fromUser = $relationship->getFromUser();
    assert(null !== $fromUser);
    $affectedUsers = [$fromUser];
    $this->broadcast($affectedUsers, $payload);
  }

  public function onUsernameChanged(User $user): void
  {
    $affectedUsers = $this->userRepository->getControllingUsersIncludingSelf(
      [$user],
      RelationshipCapability::Read->permissionsRequired(),
    );
    $payload = $this->commandRenderer->usernameChanged($user);
    $this->broadcast($affectedUsers, $payload);
  }

  /**
   * @param User[] $affectedUsers
   */
  private function broadcast(array $affectedUsers, array $payloadArray): void
  {
    $users = array_map(fn (User $user) => (string) $user->getId(), $affectedUsers);
    $payload = json_encode($payloadArray);
    assert(false !== $payload);
    $this->eventBus->post(new Event($users, self::TOPIC, $payload, sseEvent: self::TOPIC));
  }
}
