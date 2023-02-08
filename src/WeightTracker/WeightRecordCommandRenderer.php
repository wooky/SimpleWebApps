<?php

declare(strict_types=1);

namespace SimpleWebApps\WeightTracker;

use function array_key_exists;
use function assert;

use SimpleWebApps\Entity\User;
use SimpleWebApps\Entity\WeightRecord;
use Symfony\Component\Uid\Ulid;

class WeightRecordCommandRenderer
{
  public function __construct(
    private WeightRecordDataRenderer $dataRenderer,
  ) {
    // Do nothing.
  }

  /**
   * @param WeightRecord[] $weightRecords
   */
  public function initialData(User $self, array $weightRecords): array
  {
    $dataSets = [];
    foreach ($weightRecords as $weightRecord) {
      $user = $weightRecord->getOwner();
      assert(null !== $user);
      $username = $user->getUsername();
      assert(null !== $username);
      if (!array_key_exists($username, $dataSets)) {
        $dataSets[$username] = $this->dataRenderer->dataSet($user, hidden: $user !== $self);
      }
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
  public function relationshipUpdated(User $user, array $weightRecords): array
  {
    $dataPoints = array_map(fn (WeightRecord $weightRecord) => $this->dataRenderer->dataPoint($weightRecord), $weightRecords);

    return [
      'command' => 'relationship-created',
      'data' => $this->dataRenderer->dataSet($user, $dataPoints),
    ];
  }

  public function relationshipDeleted(Ulid $ulid): array
  {
    return [
      'command' => 'relationship-deleted',
      'id' => (string) $ulid,
    ];
  }
}
