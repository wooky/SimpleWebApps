<?php

declare(strict_types=1);

namespace SimpleWebApps\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use SimpleWebApps\Entity\Interface\Identifiable;
use SimpleWebApps\Entity\Interface\Imageable;
use SimpleWebApps\Entity\Mixin\IdMixin;
use SimpleWebApps\Entity\Mixin\ImageMixin;
use SimpleWebApps\Repository\BookRepository;

#[ORM\Entity(repositoryClass: BookRepository::class)]
#[Gedmo\Loggable]
class Book implements Identifiable, Imageable
{
  use IdMixin;
  use ImageMixin;

  #[ORM\Column(length: 255)]
  #[Gedmo\Versioned]
  private ?string $title = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  #[Gedmo\Versioned]
  private ?string $description = null;

  #[ORM\Column]
  #[Gedmo\Versioned]
  private ?bool $isPublic = null;

  public function getTitle(): ?string
  {
    return $this->title;
  }

  public function setTitle(string $title): self
  {
    $this->title = $title;

    return $this;
  }

  public function getDescription(): ?string
  {
    return $this->description;
  }

  public function setDescription(?string $description): self
  {
    $this->description = $description;

    return $this;
  }

  public function isPublic(): ?bool
  {
    return $this->isPublic;
  }

  public function setIsPublic(bool $isPublic): self
  {
    $this->isPublic = $isPublic;

    return $this;
  }
}
