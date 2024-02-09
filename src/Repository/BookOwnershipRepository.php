<?php

declare(strict_types=1);

namespace SimpleWebApps\Repository;

use Doctrine\Persistence\ManagerRegistry;
use SimpleWebApps\Book\BookOwnershipState;
use SimpleWebApps\Entity\BookOwnership;
use SimpleWebApps\Entity\User;

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

  /**
   * @return BookOwnership[]
   */
  public function findWithBookByOwner(User $owner): array
  {
    $qb = $this->createQueryBuilder('bo');

    return $qb
      ->select('bo', 'b')
      ->innerJoin('bo.book', 'b')
      ->where($qb->expr()->eq('bo.owner', '?1'))
      ->setParameter(1, $owner->getId(), 'ulid')
      ->getQuery()
      ->getResult();
  }

  /**
   * @return BookOwnership[]
   */
  public function findWithBookByOwnerAndState(User $owner, BookOwnershipState $state): array
  {
    $qb = $this->createQueryBuilder('bo');

    return $qb
      ->select('bo', 'b')
      ->innerJoin('bo.book', 'b')
      ->where($qb->expr()->andX(
        $qb->expr()->eq('bo.owner', '?1'),
        $qb->expr()->eq('bo.state', '?2'),
      ))
      ->setParameter(1, $owner->getId(), 'ulid')
      ->setParameter(2, $state)
      ->getQuery()
      ->getResult();
  }
}
