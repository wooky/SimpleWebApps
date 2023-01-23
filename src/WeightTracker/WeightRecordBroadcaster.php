<?php

declare(strict_types=1);

namespace SimpleWebApps\WeightTracker;

use function assert;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use SimpleWebApps\Auth\RelationshipCapability;
use SimpleWebApps\Entity\Relationship;
use SimpleWebApps\Entity\User;
use SimpleWebApps\Entity\WeightRecord;
use SimpleWebApps\Repository\UserRepository;
use Symfony\UX\Turbo\Broadcaster\BroadcasterInterface;

#[AsEntityListener(event: Events::postPersist, method: 'onWeightRecordChange', entity: WeightRecord::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'onWeightRecordChange', entity: WeightRecord::class)]
#[AsEntityListener(event: Events::postRemove, method: 'onWeightRecordChange', entity: WeightRecord::class)]
#[AsEntityListener(event: Events::postPersist, method: 'onRelationshipChange', entity: Relationship::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'onRelationshipChange', entity: Relationship::class)]
#[AsEntityListener(event: Events::postRemove, method: 'onRelationshipChange', entity: Relationship::class)]
class WeightRecordBroadcaster
{
  private const TOPIC_PREFIX = 'weight_record_';

  public function __construct(
    private readonly WeightTrackerService $weightTrackerService,
    private readonly UserRepository $userRepository,
    private BroadcasterInterface $broadcaster
  ) {
    // Do nothing.
  }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function onWeightRecordChange(WeightRecord $weightRecord, LifecycleEventArgs $event): void
    {
      $owner = $weightRecord->getOwner();
      assert(null !== $owner);
      $affectedUsers = $this->userRepository->getControllingUsersIncludingSelf($owner, RelationshipCapability::Read->permissionsRequired());
      $this->broadcast($affectedUsers);
    }

  /**
   * @SuppressWarnings(PHPMD.UnusedFormalParameter)
   */
  public function onRelationshipChange(Relationship $relationship, LifecycleEventArgs $event): void
  {
    $fromUser = $relationship->getFromUser();
    assert(null !== $fromUser);
    $affectedUsers = [$fromUser];
    $this->broadcast($affectedUsers);
  }

  /**
   * @param User[] $affectedUsers
   */
  private function broadcast(array $affectedUsers): void
  {
    foreach ($affectedUsers as $user) {
      $options = [
        'topics' => self::getTopics($user),
        'rendered_action' => json_encode($this->weightTrackerService->getRenderableDataSets($user)),
        'private' => true,
      ];
      /**
       * @psalm-suppress InvalidArgument
       */
      $this->broadcaster->broadcast($user, '', $options); // first 2 parameters are not important
    }
  }

  /**
   * @return string[]
   */
  public static function getTopics(User $user): array
  {
    return [self::TOPIC_PREFIX.((string) $user->getId())];
  }
}
