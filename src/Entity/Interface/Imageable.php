<?php

declare(strict_types=1);

namespace SimpleWebApps\Entity\Interface;

interface Imageable
{
  public function getImagePath(): ?string;

  public function setImagePath(?string $imagePath): self;
}
