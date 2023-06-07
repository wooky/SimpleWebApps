<?php

declare(strict_types=1);

namespace SimpleWebApps\Book;

use SimpleWebApps\Entity\Book;
use SimpleWebApps\Entity\BookOwnership;
use SimpleWebApps\Entity\User;
use Symfony\Component\Uid\Ulid;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

/**
 * @psalm-suppress MissingConstructor
 */
#[AsTwigComponent(self::NAME)]
class BookCard
{
  public const NAME = 'book:card';

  public BookOwnership $bookOwnership;

  #[ExposeInTemplate]
  public function getCardHtmlId(): string
  {
    return self::cardHtmlId($this->bookOwnership->getBook()->getId());
  }

  #[ExposeInTemplate]
  public function getContentHtmlId(): string
  {
    return self::contentHtmlId($this->bookOwnership->getBook()->getId());
  }

  public static function cardHtmlId(Ulid $bookId): string
  {
    return "book-$bookId";
  }

  public static function contentHtmlId(Ulid $bookId): string
  {
    return "book-content-$bookId";
  }

  /**
   * TODO.
   */
  public static function wrapInEmptyOwnership(Book $book, ?User $owner = null): BookOwnership
  {
    return (new BookOwnership())
      ->setBook($book)
      ->setOwner($owner)
    ;
  }
}
