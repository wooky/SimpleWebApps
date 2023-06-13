<?php

declare(strict_types=1);

namespace SimpleWebApps\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use SimpleWebApps\Repository\BookRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Component\Uid\Ulid;

use function assert;

#[ORM\Entity(repositoryClass: BookRepository::class)]
#[Gedmo\Loggable]
#[Gedmo\Uploadable(filenameGenerator: 'SHA1', allowedTypes: 'image/jpeg')]
class Book implements Identifiable
{
  #[ORM\Id]
  #[ORM\Column(type: 'ulid', unique: true)]
  #[ORM\GeneratedValue(strategy: 'CUSTOM')]
  #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
  private ?Ulid $id = null;

  #[ORM\Column(length: 255)]
  #[Gedmo\Versioned]
  private ?string $title = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  #[Gedmo\Versioned]
  private ?string $description = null;

  #[ORM\Column]
  #[Gedmo\Versioned]
  private ?bool $isPublic = null;

  #[ORM\Column(length: 64, nullable: true)]
  #[Gedmo\UploadableFileName]
  #[Gedmo\Versioned]
  private ?string $imagePath = null;

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

  public function isPublic(): ?bool
  {
    return $this->isPublic;
  }

  public function setIsPublic(bool $isPublic): self
  {
    $this->isPublic = $isPublic;

    return $this;
  }

  public function getImagePath(): ?string
  {
    return $this->imagePath;
  }

  public function setImagePath(string $imagePath): self
  {
    $this->imagePath = $imagePath;

    return $this;
  }
}
