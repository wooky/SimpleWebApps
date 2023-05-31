<?php

declare(strict_types=1);

namespace SimpleWebApps\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @template T
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
   * @param T entity
   */
  public function remove($entity, bool $flush = false): void
  {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }
}
