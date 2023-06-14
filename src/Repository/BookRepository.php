<?php

declare(strict_types=1);

namespace SimpleWebApps\Repository;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use SimpleWebApps\Entity\Book;
use SimpleWebApps\Entity\BookOwnership;
use SimpleWebApps\Entity\User;

/**
 * @extends AbstractRepository<Book>
 */
class BookRepository extends AbstractRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, Book::class);
  }

  /**
   * @return Book[]
   */
  public function findBooksNotBelongingToUser(User $user): array
  {
    $qb = $this->createQueryBuilder('b');

    return $qb
      ->distinct()
      ->leftJoin(BookOwnership::class, 'bo', Join::WITH, $qb->expr()->andX(
        $qb->expr()->eq('bo.book', 'b'),
        $qb->expr()->eq('bo.owner', '?1'),
      ))
      ->where($qb->expr()->isNull('bo.owner'))
      ->andWhere($qb->expr()->eq('b.isPublic', true))
      ->setParameter(1, $user->getId(), 'ulid')
      ->getQuery()
      ->getResult();
  }

//    /**
//     * @return Book[] Returns an array of Book objects
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

//    public function findOneBySomeField($value): ?Book
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
