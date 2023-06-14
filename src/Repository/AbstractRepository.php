<?php

declare(strict_types=1);

namespace SimpleWebApps\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @template T of object
 *
 * @extends ServiceEntityRepository<T>
 *
 * @method ?T  find($id, $lockMode = null, $lockVersion = null)
 * @method ?T  findOneBy(array $criteria, array $orderBy = null)
 * @method T[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
abstract class AbstractRepository extends ServiceEntityRepository
{
  /**
   * @param class-string<T> $entityClass
   */
  public function __construct(ManagerRegistry $registry, string $entityClass)
  {
    parent::__construct($registry, $entityClass);
  }

  /**
   * @param T $entity
   */
  public function save($entity, bool $flush = false): void
  {
    $this->getEntityManager()->persist($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  /**
   * @param T $entity
   */
  public function remove($entity, bool $flush = false): void
  {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }
}
