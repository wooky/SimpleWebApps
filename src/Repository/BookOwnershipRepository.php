<?php

declare(strict_types=1);

namespace SimpleWebApps\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use SimpleWebApps\Entity\BookOwnership;

/**
 * @extends ServiceEntityRepository<BookOwnership>
 *
 * @method BookOwnership|null find($id, $lockMode = null, $lockVersion = null)
 * @method BookOwnership|null findOneBy(array $criteria, array $orderBy = null)
 * @method BookOwnership[]    findAll()
 * @method BookOwnership[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookOwnershipRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, BookOwnership::class);
  }

  public function save(BookOwnership $entity, bool $flush = false): void
  {
    $this->getEntityManager()->persist($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  public function remove(BookOwnership $entity, bool $flush = false): void
  {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
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
