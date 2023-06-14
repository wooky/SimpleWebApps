<?php

declare(strict_types=1);

namespace SimpleWebApps\Repository;

use Doctrine\Persistence\ManagerRegistry;
use SimpleWebApps\Entity\Artefact;

/**
 * @extends AbstractRepository<Artefact>
 */
class ArtefactRepository extends AbstractRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, Artefact::class);
  }
}
