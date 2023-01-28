<?php

declare(strict_types=1);

use Symfony\Component\Uid\Ulid;

readonly class ChartDataPoint
{
  /**
   * @SuppressWarnings(PHPMD.ShortVariable)
   */
  public function __construct(
    public Ulid $id,
    public int $x,
    public int $y,
  ) {
    // Do nothing.
  }
}
