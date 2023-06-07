<?php

declare(strict_types=1);

namespace SimpleWebApps\Book;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use SimpleWebApps\Entity\Book;
use Symfony\Component\Uid\Ulid;

#[AsEntityListener(event: Events::postUpdate, method: 'onBookUpdated', entity: Book::class)]
#[AsEntityListener(event: Events::preRemove, method: 'onBookPreRemoved', entity: Book::class)]
#[AsEntityListener(event: Events::postRemove, method: 'onBookDeleted', entity: Book::class)]
class BookListener
{
  private ?Ulid $lastBookIdRemoved = null;

  public function __construct(
    private BookBroadcaster $broadcaster
  ) {
    // Do nothing.
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
