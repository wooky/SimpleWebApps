<?php

declare(strict_types=1);

namespace SimpleWebApps\Entity\Interface;

use Symfony\Component\Uid\Ulid;

interface Identifiable
{
  public function getId(): Ulid;

  public function getIdOrNull(): ?Ulid;
}
