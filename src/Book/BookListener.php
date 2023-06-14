<?php

declare(strict_types=1);

namespace SimpleWebApps\Book;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use SimpleWebApps\Entity\Book;
use SimpleWebApps\Entity\BookOwnership;
use Symfony\Component\Uid\Ulid;

#[AsEntityListener(event: Events::postPersist, method: 'onBookOwnershipCreated', entity: BookOwnership::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'onBookOwnershipUpdated', entity: BookOwnership::class)]
#[AsEntityListener(event: Events::postRemove, method: 'onBookOwnershipDeleted', entity: BookOwnership::class)]
// skip book create because it's done as part of book ownership creation
#[AsEntityListener(event: Events::postUpdate, method: 'onBookUpdated', entity: Book::class)]
#[AsEntityListener(event: Events::preRemove, method: 'onBookPreRemoved', entity: Book::class)]
#[AsEntityListener(event: Events::postRemove, method: 'onBookDeleted', entity: Book::class)]
class BookListener
{
  private ?Ulid $lastBookIdRemoved = null;

  public function __construct(
    private BookBroadcaster $broadcaster,
  ) {
    // Do nothing.
  }

  public function onBookOwnershipCreated(BookOwnership $bookOwnership): void
  {
    $this->broadcaster->onBookOwnershipCreated($bookOwnership);
  }

  public function onBookOwnershipUpdated(BookOwnership $bookOwnership): void
  {
    $this->broadcaster->onBookOwnershipUpdated($bookOwnership);
  }

  public function onBookOwnershipDeleted(BookOwnership $bookOwnership): void
  {
    $this->broadcaster->onBookOwnershipDeleted($bookOwnership);
  }

  public function onBookUpdated(Book $book): void
  {
    $this->broadcaster->onBookUpdated($book);
  }

  public function onBookPreRemoved(Book $book): void
  {
    $this->lastBookIdRemoved = $book->getId();
  }

  public function onBookDeleted(Book $book): void
  {
    if ($this->lastBookIdRemoved) {
      $this->broadcaster->onBookDeleted($book, $this->lastBookIdRemoved);
      $this->lastBookIdRemoved = null;
    }
  }
}
