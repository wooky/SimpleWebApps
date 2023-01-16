<?php declare(strict_types = 1);
namespace SimpleWebApps\WeightTracker;

use SimpleWebApps\Auth\RelationshipCapability;
use SimpleWebApps\Entity\User;
use SimpleWebApps\Repository\UserRepository;
use SimpleWebApps\Repository\WeightRecordRepository;
use Symfony\Bundle\SecurityBundle\Security;

class WeightTrackerService
{
  public function __construct(
    private Security $security,
    private UserRepository $userRepository,
    private WeightRecordRepository $weightRecordRepository,
  ) {
    // Do nothing.
  }

  public function getRenderableDataSets(User $self): array
  {
    $users = $this->userRepository->getControlledUsersIncludingSelf($self, RelationshipCapability::Read->permissionsRequired());
    $userIds = [];
    $dataSets = [];
    foreach ($users as $user) {
      $username = $user->getUsername();
      assert($username !== null);
      $userId = $user->getId()?->toBinary();
      assert($userId !== null);
      $userIds[] = $userId;
      $dataSets[$username] = [
        'label' => $username,
        'data' => [],
        '__self' => $user == $self,
        // TODO editable
      ];
    }

    $dataPoints = $this->weightRecordRepository->getDataPoints($userIds);
    foreach ($dataPoints as $dataPoint) {
      $username = $dataPoint->getOwner()?->getUsername();
      assert($username !== null);
      $timestamp = $dataPoint->getDate()?->getTimestamp();
      assert($timestamp !== null);
      $dataSets[$username]['data'][] = [
        'id' => $dataPoint->getId(),
        'x' => $timestamp * 1000,
        'y' => $dataPoint->getWeight(),
      ];
    }

    return array_values($dataSets);
  }
}

