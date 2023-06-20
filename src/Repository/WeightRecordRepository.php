<?php

declare(strict_types=1);

namespace SimpleWebApps\Repository;

use Doctrine\Persistence\ManagerRegistry;
use SimpleWebApps\Entity\WeightRecord;

/**
 * @extends AbstractRepository<WeightRecord>
 */
class WeightRecordRepository extends AbstractRepository
{
  public function __construct(readonly ManagerRegistry $registry)
  {
    parent::__construct($registry, WeightRecord::class);
  }

  /**
   * @param string[] $owners Owner ULIDs as binary text
   *
   * @return WeightRecord[]
   */
  public function getDataPoints(array $owners): array
  {
    $qb = $this->createQueryBuilder('wr');

    return $qb
        ->select()
        ->innerJoin('wr.owner', 'o')
        ->where($qb->expr()->in('o', '?1'))
        ->orderBy('wr.date', 'ASC')
        ->setParameter(1, $owners)
        ->getQuery()
        ->getResult();
  }
}
