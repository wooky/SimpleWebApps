<?php

declare(strict_types=1);

namespace SimpleWebApps\Entity\Mixin;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Component\Uid\Ulid;

use function assert;

trait IdMixin
{
  #[ORM\Id]
  #[ORM\Column(type: 'ulid', unique: true)]
  #[ORM\GeneratedValue(strategy: 'CUSTOM')]
  #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
  private ?Ulid $id = null;

  public function getId(): Ulid
  {
    assert(null !== $this->id);

    return $this->id;
  }

  public function getIdOrNull(): ?Ulid
  {
    return $this->id;
  }
}
