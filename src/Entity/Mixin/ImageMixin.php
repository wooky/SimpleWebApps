<?php

declare(strict_types=1);

namespace SimpleWebApps\Entity\Mixin;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use SimpleWebApps\Entity\Artefact;

trait ImageMixin
{
  // TODO does the cascade do anything, considering that Uploadable is a fragile little snowflake?
  #[ORM\OneToOne(cascade: ['persist', 'remove'])]
  #[Gedmo\Versioned]
  private ?Artefact $image = null;

  public function getImage(): ?Artefact
  {
    return $this->image;
  }

  public function setImage(?Artefact $image): self
  {
    $this->image = $image;

    return $this;
  }
}
