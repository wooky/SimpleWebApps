<?php

declare(strict_types=1);

namespace SimpleWebApps\WeightTracker;

use InvalidArgumentException;
use SimpleWebApps\Entity\User;
use SimpleWebApps\Entity\WeightRecord;

use function assert;

readonly class WeightRecordDataRenderer
{
  /**
   * @return array{id: string, x: int, y: int, owner: string}
   */
  public function dataPoint(WeightRecord $weightRecord): array
  {
    $timestamp = $weightRecord->getDate()?->getTimestamp();
    assert(null !== $timestamp);

    return [
      'id' => (string) $weightRecord->getId(),
      'x' => $timestamp * 1000,
      'y' => $weightRecord->getWeight() ?? throw new InvalidArgumentException('Weight record has no weight'),
      'owner' => (string) $weightRecord->getOwner()?->getId(),
    ];
  }

  /**
   * TODO editable.
   *
   * @return array{id: string, label: string, data: array, hidden?: bool}
   */
  public function dataSet(User $user, array $data = [], ?bool $hidden = null): array
  {
    $payload = [
      'id' => (string) $user->getId(),
      'label' => $user->getUsername() ?? throw new InvalidArgumentException('User has no username'),
      'data' => $data,
    ];
    if (null !== $hidden) {
      $payload['hidden'] = $hidden;
    }

    return $payload;
  }
}
