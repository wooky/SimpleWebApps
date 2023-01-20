<?php

namespace SimpleWebApps\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use SimpleWebApps\Auth\RelationshipCapability;
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
    public function __construct(ManagerRegistry $registry)
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
            ->select()
            ->innerJoin('rel.fromUser', 'fu')
            ->innerJoin('rel.toUser', 'tu')
            ->where($qb->expr()->eq('fu.id', '?1'))
            ->orWhere($qb->expr()->eq('tu.id', '?1'))
            ->setParameter(1, $user->getId(), 'ulid')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param RelationshipCapability[] $capabilities
     */
    public function findActiveRelationship(User $fromUser, User $toUser, array $capabilities): ?Relationship
    {
        $qb = $this->createQueryBuilder('rel');
        return $qb
            ->innerJoin('rel.fromUser', 'fu')
            ->innerJoin('rel.toUser', 'tu')
            ->where($qb->expr()->eq('fu.id', '?1'))
            ->andWhere($qb->expr()->eq('tu.id', '?2'))
            ->andWhere($qb->expr()->in('rel.capability', '?3'))
            ->andWhere($qb->expr()->eq('rel.active', true))
            ->setParameter(1, $fromUser->getId(), 'ulid')
            ->setParameter(2, $toUser->getId(), 'ulid')
            ->setParameter(3, $capabilities)
            ->getQuery()
            ->getOneOrNullResult();
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

//    /**
//     * @return Relationship[] Returns an array of Relationship objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Relationship
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
