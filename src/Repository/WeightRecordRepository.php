<?php

declare(strict_types=1);

namespace SimpleWebApps\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use SimpleWebApps\Entity\WeightRecord;

/**
 * @extends ServiceEntityRepository<WeightRecord>
 *
 * @method WeightRecord|null find($id, $lockMode = null, $lockVersion = null)
 * @method WeightRecord|null findOneBy(array $criteria, array $orderBy = null)
 * @method WeightRecord[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WeightRecordRepository extends ServiceEntityRepository
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

  public function save(WeightRecord $entity, bool $flush = false): void
  {
    $this->getEntityManager()->persist($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  public function remove(WeightRecord $entity, bool $flush = false): void
  {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

//    /**
//     * @return WeightRecord[] Returns an array of WeightRecord objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('w')
//            ->andWhere('w.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('w.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?WeightRecord
//    {
//        return $this->createQueryBuilder('w')
//            ->andWhere('w.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
