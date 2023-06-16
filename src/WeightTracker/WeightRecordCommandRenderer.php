<?php

declare(strict_types=1);

namespace SimpleWebApps\WeightTracker;

use SimpleWebApps\Entity\User;
use SimpleWebApps\Entity\WeightRecord;
use Symfony\Component\Uid\Ulid;

use function assert;

class WeightRecordCommandRenderer
{
  public function __construct(
    private WeightRecordDataRenderer $dataRenderer,
  ) {
    // Do nothing.
  }

  /**
   * @param User[]         $users
   * @param WeightRecord[] $weightRecords
   */
  public function initialData(User $self, array $users, array $weightRecords): array
  {
    $dataSets = [];
    foreach ($users as $user) {
      $username = $user->getUsername();
      assert(null !== $username);
      $dataSets[$username] = $this->dataRenderer->dataSet($user, hidden: $user !== $self);
    }
    foreach ($weightRecords as $weightRecord) {
      $user = $weightRecord->getOwner();
      assert(null !== $user);
      $username = $user->getUsername();
      assert(null !== $username);
      $dataSets[$username]['data'][] = $this->dataRenderer->dataPoint($weightRecord);
    }

    return [
      'command' => 'initial-data',
      'data' => array_values($dataSets),
    ];
  }

  public function weightRecordCreated(WeightRecord $weightRecord): array
  {
    return [
      'command' => 'weight-record-created',
      'data' => $this->dataRenderer->dataPoint($weightRecord),
    ];
  }

  public function weightRecordUpdated(WeightRecord $weightRecord): array
  {
    return [
      'command' => 'weight-record-updated',
      'data' => $this->dataRenderer->dataPoint($weightRecord),
    ];
  }

  public function weightRecordDeleted(Ulid $ulid): array
  {
    return [
      'command' => 'weight-record-deleted',
      'id' => (string) $ulid,
    ];
  }

  /**
   * @param WeightRecord[] $weightRecords
   */
  public function relationshipActivated(User $user, array $weightRecords): array
  {
    $dataPoints = array_map(
      fn (WeightRecord $weightRecord) => $this->dataRenderer->dataPoint($weightRecord),
      $weightRecords,
    );

    return [
      'command' => 'relationship-activated',
      'data' => $this->dataRenderer->dataSet($user, $dataPoints, true),
    ];
  }

  public function relationshipDeleted(Ulid $userUlid): array
  {
    return [
      'command' => 'relationship-deleted',
      'id' => (string) $userUlid,
    ];
  }

  public function usernameChanged(User $user): array
  {
    return [
      'command' => 'username-updated',
      'id' => (string) $user->getId(),
      'username' => $user->getUsername(),
    ];
  }
}
