<?php

declare(strict_types=1);

namespace SimpleWebApps\Entity;

use Symfony\Component\Uid\Ulid;

interface Identifiable
{
  public function getId(): Ulid;

  public function getIdOrNull(): ?Ulid;
}
