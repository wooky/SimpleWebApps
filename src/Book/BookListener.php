<?php

declare(strict_types=1);

namespace SimpleWebApps\Book;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use SimpleWebApps\Entity\Book;

#[AsEntityListener(event: Events::postUpdate, method: 'onBookUpdated', entity: Book::class)]
class BookListener
{
  public function __construct(
    private BookBroadcaster $broadcaster
  ) {
    // Do nothing.
  }

  public function onBookUpdated(Book $book): void
  {
    $this->broadcaster->onBookUpdated($book);
  }
}
