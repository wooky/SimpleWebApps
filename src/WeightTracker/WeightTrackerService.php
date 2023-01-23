<?php

declare(strict_types=1);

namespace SimpleWebApps\WeightTracker;

use function assert;

use SimpleWebApps\Auth\RelationshipCapability;
use SimpleWebApps\Entity\User;
use SimpleWebApps\Repository\UserRepository;
use SimpleWebApps\Repository\WeightRecordRepository;

readonly class WeightTrackerService
{
  public function __construct(
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
      assert(null !== $username);
      $userId = $user->getId()?->toBinary();
      assert(null !== $userId);
      $userIds[] = $userId;
      $dataSets[$username] = [
        'label' => $username,
        'data' => [],
        '__self' => $user === $self,
        // TODO editable
      ];
    }

    $dataPoints = $this->weightRecordRepository->getDataPoints($userIds);
    foreach ($dataPoints as $dataPoint) {
      $username = $dataPoint->getOwner()?->getUsername();
      assert(null !== $username);
      $timestamp = $dataPoint->getDate()?->getTimestamp();
      assert(null !== $timestamp);
      $dataSets[$username]['data'][] = [
        'id' => $dataPoint->getId(),
        'x' => $timestamp * 1000,
        'y' => $dataPoint->getWeight(),
      ];
    }

    return array_values($dataSets);
  }
}
