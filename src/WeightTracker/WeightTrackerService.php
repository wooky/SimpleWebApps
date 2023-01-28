<?php

declare(strict_types=1);

namespace SimpleWebApps\WeightTracker;

use function assert;

use ChartDataPoint;
use ChartDataSet;
use RuntimeException;
use SimpleWebApps\Auth\RelationshipCapability;
use SimpleWebApps\Entity\User;
use SimpleWebApps\Repository\UserRepository;
use SimpleWebApps\Repository\WeightRecordRepository;

class WeightTrackerService
{
  public function __construct(
    private readonly UserRepository $userRepository,
    private readonly WeightRecordRepository $weightRecordRepository,
  ) {
    // Do nothing.
  }

  /**
   * @return ChartDataSet[]
   */
  public function getRenderableDataSets(User $self): array
  {
    $users = $this->userRepository->getControlledUsersIncludingSelf($self, RelationshipCapability::Read->permissionsRequired());
/** @var string[] */ $userIds = [];
/** @var array<string, ChartDataSet> */ $dataSets = [];
    foreach ($users as $user) {
      $username = $user->getUsername();
      assert(null !== $username);
      $userId = $user->getId()?->toBinary();
      assert(null !== $userId);
      $userIds[] = $userId;
      $dataSets[$username] = new ChartDataSet(
        label: $username,
        data: [],
        __self: $user === $self,
        // TODO editable
      );
    }

    $dataPoints = $this->weightRecordRepository->findBy(['owner' => $userIds], orderBy: ['date' => 'ASC']);
    foreach ($dataPoints as $dataPoint) {
      $username = $dataPoint->getOwner()?->getUsername();
      assert(null !== $username);
      $timestamp = $dataPoint->getDate()?->getTimestamp();
      assert(null !== $timestamp);
      $dataSets[$username]->data[] = new ChartDataPoint(
        id: $dataPoint->getId(),
        x: $timestamp * 1000,
        y: $dataPoint->getWeight() ?? throw new RuntimeException('Data point has no weight'),
      );
    }

    return array_values($dataSets);
  }
}
