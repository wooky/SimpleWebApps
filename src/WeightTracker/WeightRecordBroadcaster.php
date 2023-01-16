<?php declare(strict_types = 1);
namespace SimpleWebApps\WeightTracker;

use Psr\Log\LoggerInterface;
use RuntimeException;
use SimpleWebApps\Auth\RelationshipCapability;
use SimpleWebApps\Entity\Relationship;
use SimpleWebApps\Entity\User;
use SimpleWebApps\Entity\WeightRecord;
use SimpleWebApps\Repository\UserRepository;
use Symfony\Component\Mercure\HubInterface;
use Symfony\UX\Turbo\Bridge\Mercure\Broadcaster;
use Symfony\UX\Turbo\Broadcaster\BroadcasterInterface;

class WeightRecordBroadcaster implements BroadcasterInterface
{
  private const TOPIC_PREFIX = 'weight_record_';

  private readonly BroadcasterInterface $mercureBroadcaster;

  public function __construct(
    private readonly WeightTrackerService $weightTrackerService,
    private readonly UserRepository $userRepository,
    private readonly LoggerInterface $logger,
    HubInterface $hub,
  ) {
    $this->mercureBroadcaster = new Broadcaster('default', $hub);
  }

  public function broadcast(object $entity, string $action, array $options): void
  {
    /** @var User[] */ $users = [];
    if ($entity instanceof WeightRecord) {
      $users = $this->getAffectedUsersOfWeightRecord($entity);
    } elseif ($entity instanceof Relationship) {
      $users = $this->getAffectedUsersOfRelationship($entity);
    }

    foreach ($users as $user) {
      $options['topics'] = self::getTopics($user);
      $options['rendered_action'] = json_encode($this->weightTrackerService->getRenderableDataSets($user));
      $this->mercureBroadcaster->broadcast($user, '', $options); // first 2 parameters are not important
    }
  }

  /**
   * @return string[]
   */
  public static function getTopics(User $user): array
  {
    return [self::TOPIC_PREFIX . ($user->getId() ?? throw new RuntimeException('User has no ID'))];
  }

  /**
   * @return User[]
   */
  private function getAffectedUsersOfWeightRecord(WeightRecord $weightRecord): array
  {
    $owner = $weightRecord->getOwner();
    assert($owner !== null);
    return $this->userRepository->getControllingUsersIncludingSelf($owner, RelationshipCapability::Read->permissionsRequired());
  }

  /**
   * @return User[]
   */
  private function getAffectedUsersOfRelationship(Relationship $relationship): array
  {
    $fromUser = $relationship->getFromUser();
    assert($fromUser !== null);
    return [$fromUser];
  }
}

