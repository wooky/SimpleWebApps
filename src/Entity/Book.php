<?php

declare(strict_types=1);

namespace SimpleWebApps\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use SimpleWebApps\Book\BookPublicity;
use SimpleWebApps\Entity\Interface\Identifiable;
use SimpleWebApps\Entity\Interface\Imageable;
use SimpleWebApps\Entity\Mixin\IdMixin;
use SimpleWebApps\Entity\Mixin\ImageMixin;
use SimpleWebApps\Repository\BookRepository;

use function assert;

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
  private BookPublicity $publicity = BookPublicity::Private;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: false)]
  private ?User $creator = null;

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

  public function getPublicity(): BookPublicity
  {
    return $this->publicity;
  }

  public function setPublicity(BookPublicity $publicity): self
  {
    $this->publicity = $publicity;

    return $this;
  }

  public function getCreator(): User
  {
    assert(null !== $this->creator);

    return $this->creator;
  }

  public function setCreator(?User $creator): static
  {
    $this->creator = $creator;

    return $this;
  }
}
