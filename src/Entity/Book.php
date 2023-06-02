<?php

declare(strict_types=1);

namespace SimpleWebApps\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use SimpleWebApps\Repository\BookRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Component\Uid\Ulid;

use function assert;

#[ORM\Entity(repositoryClass: BookRepository::class)]
class Book implements Identifiable
{
  #[ORM\Id]
  #[ORM\Column(type: 'ulid', unique: true)]
  #[ORM\GeneratedValue(strategy: 'CUSTOM')]
  #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
  private ?Ulid $id = null;

  #[ORM\Column(length: 255)]
  private ?string $title = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $description = null;

  #[ORM\Column]
  private bool $hasImage = false;

  #[ORM\Column]
  private ?bool $isPublic = null;

  public function getId(): Ulid
  {
    assert(null !== $this->id);

    return $this->id;
  }

  public function getIdOrNull(): ?Ulid
  {
    return $this->id;
  }

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

  public function hasImage(): bool
  {
    return $this->hasImage;
  }

  public function setHasImage(bool $hasImage): self
  {
    $this->hasImage = $hasImage;

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
