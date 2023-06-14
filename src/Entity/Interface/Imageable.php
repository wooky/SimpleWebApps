<?php

declare(strict_types=1);

namespace SimpleWebApps\Entity\Interface;

use SimpleWebApps\Entity\Artefact;

interface Imageable
{
  public function getImage(): ?Artefact;

  public function setImage(?Artefact $image): self;
}
