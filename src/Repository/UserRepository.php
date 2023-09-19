<?php

declare(strict_types=1);

namespace SimpleWebApps\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use SimpleWebApps\Auth\RelationshipCapability;
use SimpleWebApps\Entity\Relationship;
use SimpleWebApps\Entity\User;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

use function get_class;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
  public function __construct(readonly ManagerRegistry $registry)
  {
    parent::__construct($registry, User::class);
  }

  /**
   * @param User[]                   $self
   * @param RelationshipCapability[] $capabilitiesAllowed
   *
   * @return User[]
   */
  public function getControlledUsersIncludingSelf(array $self, array $capabilitiesAllowed): array
  {
    return $this->getControlledUsersIncludingSelfQuery($self, $capabilitiesAllowed)
        ->getQuery()
        ->getResult();
  }

  /**
   * @param User[]                   $self
   * @param RelationshipCapability[] $capabilitiesAllowed
   */
  public function getControlledUsersIncludingSelfQuery(array $self, array $capabilitiesAllowed): QueryBuilder
  {
    $selfIds = array_map(static fn (User $user) => $user->getId()?->toBinary(), $self);
    $qb = $this->createQueryBuilder('u');

    return $qb
        ->distinct()
        ->leftJoin(Relationship::class, 'rel', Expr\Join::WITH, $qb->expr()->eq('rel.toUser', 'u.id'))
        ->where($qb->expr()->in('u.id', '?1'))
        ->orWhere($qb->expr()->andX(
          $qb->expr()->in('rel.fromUser', '?1'),
          $qb->expr()->in('rel.capability', '?2'),
          $qb->expr()->eq('rel.active', true),
        ))
        ->setParameter(1, $selfIds)
        ->setParameter(2, $capabilitiesAllowed)
    ;
  }

  /**
   * @param User[]                   $self
   * @param RelationshipCapability[] $capabilitiesRequired
   *
   * @return User[]
   */
  public function getControllingUsersIncludingSelf(array $self, array $capabilitiesRequired): array
  {
    $selfIds = array_map(static fn (User $user) => $user->getId()?->toBinary(), $self);
    $qb = $this->createQueryBuilder('u');

    return $qb
        ->distinct()
        ->leftJoin(Relationship::class, 'rel', Expr\Join::WITH, $qb->expr()->in('rel.fromUser', 'u.id'))
        ->where($qb->expr()->in('u.id', '?1'))
        ->orWhere($qb->expr()->andX(
          $qb->expr()->in('rel.toUser', '?1'),
          $qb->expr()->in('rel.capability', '?2'),
          $qb->expr()->eq('rel.active', true),
        ))
        ->setParameter(1, $selfIds)
        ->setParameter(2, $capabilitiesRequired)
        ->getQuery()
        ->getResult();
  }

  /**
   * @return User[]
   */
  public function getAllInterestedParties(User $self): array
  {
    $qb = $this->createQueryBuilder('u');

    return $qb
      ->distinct()
      ->leftJoin(Relationship::class, 'rel', Expr\Join::WITH, $qb->expr()->orX(
        $qb->expr()->eq('rel.fromUser', 'u.id'),
        $qb->expr()->eq('rel.toUser', 'u.id'),
      ))
      ->where($qb->expr()->orX(
        $qb->expr()->eq('rel.fromUser', '?1'),
        $qb->expr()->eq('rel.toUser', '?1'),
      ))
      ->setParameter(1, $self->getId(), 'ulid')
      ->getQuery()
      ->getResult();
  }

  public function save(User $entity, bool $flush = false): void
  {
    $this->getEntityManager()->persist($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  public function remove(User $entity, bool $flush = false): void
  {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  /**
   * Used to upgrade (rehash) the user's password automatically over time.
   */
  public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
  {
    if (!$user instanceof User) {
      throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
    }

    $user->setPassword($newHashedPassword);

    $this->save($user, true);
  }
}
