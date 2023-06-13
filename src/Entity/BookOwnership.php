<?php

declare(strict_types=1);

namespace SimpleWebApps\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use SimpleWebApps\Auth\Ownable;
use SimpleWebApps\Book\BookOwnershipState;
use SimpleWebApps\Entity\Interface\Identifiable;
use SimpleWebApps\Entity\Mixin\IdMixin;
use SimpleWebApps\Repository\BookOwnershipRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use function assert;

#[ORM\Entity(repositoryClass: BookOwnershipRepository::class)]
#[ORM\UniqueConstraint(fields: ['owner', 'book'])]
#[UniqueEntity(['owner', 'book'], message: 'books.ownership_exists')]
#[Gedmo\Loggable]
class BookOwnership implements Identifiable, Ownable
{
  use IdMixin;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: false)]
  private ?User $owner = null;

  #[ORM\ManyToOne(cascade: ['persist'])]
  #[ORM\JoinColumn(nullable: false)]
  private ?Book $book = null;

  #[ORM\Column(type: 'string', enumType: BookOwnershipState::class)]
  #[Gedmo\Versioned]
  private BookOwnershipState $state = BookOwnershipState::Own;

  public function getOwner(): ?User
  {
    return $this->owner;
  }

  public function setOwner(?User $owner): self
  {
    $this->owner = $owner;

    return $this;
  }

  public function getBook(): Book
  {
    assert(null !== $this->book);

    return $this->book;
  }

  public function setBook(Book $book): self
  {
    $this->book = $book;

    return $this;
  }

  public function getState(): BookOwnershipState
  {
    return $this->state;
  }

  public function setState(BookOwnershipState $state): self
  {
    $this->state = $state;

    return $this;
  }
}
