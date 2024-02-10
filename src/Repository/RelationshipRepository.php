<?php

declare(strict_types=1);

namespace SimpleWebApps\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use SimpleWebApps\Entity\Relationship;
use SimpleWebApps\Entity\User;

/**
 * @extends ServiceEntityRepository<Relationship>
 *
 * @method Relationship|null find($id, $lockMode = null, $lockVersion = null)
 * @method Relationship|null findOneBy(array $criteria, array $orderBy = null)
 * @method Relationship[]    findAll()
 * @method Relationship[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RelationshipRepository extends ServiceEntityRepository
{
  public function __construct(readonly ManagerRegistry $registry)
  {
    parent::__construct($registry, Relationship::class);
  }

  /**
   * @return Relationship[]
   */
  public function findBidirectionalRelationships(User $user): array
  {
    $qb = $this->createQueryBuilder('rel');

    return $qb
        ->select('rel', 'fu', 'tu')
        ->innerJoin('rel.fromUser', 'fu')
        ->innerJoin('rel.toUser', 'tu')
        ->where($qb->expr()->eq('fu.id', '?1'))
        ->orWhere($qb->expr()->eq('tu.id', '?1'))
        ->setParameter(1, $user->getId(), 'ulid')
        ->getQuery()
        ->getResult();
  }

  public function save(Relationship $entity, bool $flush = false): void
  {
    $this->getEntityManager()->persist($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  public function remove(Relationship $entity, bool $flush = false): void
  {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }
}
