<?php

declare(strict_types=1);

namespace SimpleWebApps\Entity;

use Doctrine\ORM\Mapping as ORM;
use SimpleWebApps\Auth\Ownable;
use SimpleWebApps\Book\BookOwnershipState;
use SimpleWebApps\Repository\BookOwnershipRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: BookOwnershipRepository::class)]
class BookOwnership implements Ownable
{
  #[ORM\Id]
  #[ORM\Column(type: 'ulid', unique: true)]
  #[ORM\GeneratedValue(strategy: 'CUSTOM')]
  #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
  private ?Ulid $id = null;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: false)]
  private ?User $owner = null;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: false)]
  private ?Book $book = null;

  #[ORM\Column(type: 'string', enumType: BookOwnershipState::class)]
  private ?BookOwnershipState $state = null;

  public function getId(): ?Ulid
  {
    return $this->id;
  }

  public function getOwner(): ?User
  {
    return $this->owner;
  }

  public function setOwner(?User $owner): self
  {
    $this->owner = $owner;

    return $this;
  }

  public function getBook(): ?Book
  {
    return $this->book;
  }

  public function setBook(?Book $book): self
  {
    $this->book = $book;

    return $this;
  }

  public function getState(): ?BookOwnershipState
  {
    return $this->state;
  }

  public function setState(BookOwnershipState $state): self
  {
    $this->state = $state;

    return $this;
  }
}
