<?php

declare(strict_types=1);

// TODO make this readonly, requires refactoring of WeightTrackerService
final class ChartDataSet
{
  /**
   * @param ChartDataPoint[] $data
   *
   * @SuppressWarnings(PHPMD.CamelCaseParameterName)
   */
  public function __construct(
    public readonly string $label,
    public array $data,
    public readonly bool $__self,
    // TODO editable
  ) {
    // Do nothing.
  }
}
