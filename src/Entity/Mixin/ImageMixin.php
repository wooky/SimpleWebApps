<?php

declare(strict_types=1);

namespace SimpleWebApps\Entity\Mixin;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

trait ImageMixin
{
  #[ORM\Column(length: 64, nullable: true)]
  #[Gedmo\UploadableFileName]
  #[Gedmo\Versioned]
  private ?string $imagePath = null;

  public function getImagePath(): ?string
  {
    return $this->imagePath;
  }

  public function setImagePath(?string $imagePath): self
  {
    $this->imagePath = $imagePath;

    return $this;
  }
}
