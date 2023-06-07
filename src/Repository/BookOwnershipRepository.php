<?php

declare(strict_types=1);

namespace SimpleWebApps\Repository;

use Doctrine\Persistence\ManagerRegistry;
use SimpleWebApps\Entity\BookOwnership;

/**
 * @extends AbstractRepository<BookOwnership>
 * TODO psalm ignores inherited @methods, but why?
 *
 * @method ?BookOwnership  find($id, $lockMode = null, $lockVersion = null)
 * @method ?BookOwnership  findOneBy(array $criteria, array $orderBy = null)
 * @method BookOwnership[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method int             count(array $criteria)
 */
class BookOwnershipRepository extends AbstractRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, BookOwnership::class);
  }

//    /**
//     * @return BookOwnership[] Returns an array of BookOwnership objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?BookOwnership
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
