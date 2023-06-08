<?php

declare(strict_types=1);

namespace SimpleWebApps\Book;

use RuntimeException;
use SimpleWebApps\Entity\Book;
use SimpleWebApps\Entity\BookOwnership;
use SimpleWebApps\Entity\User;
use Symfony\Component\Uid\Ulid;

readonly class BookRenderingUtilities
{
  public const BOOK_LIST_CLASS_PREFIX = 'book-list-';
  public const BOOK_LIST_CLASS_PUBLIC = self::BOOK_LIST_CLASS_PREFIX.'public';

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

  /**
   * @return ?string[]
   */
  public static function privateListClasses(BookViewFilter $bookViewFilter, User $user): ?array
  {
    if (BookViewFilter::All === $bookViewFilter) {
      return array_map(
        fn (BookOwnershipState $bookOwnershipState) => self::composePrivateListClass($user, $bookOwnershipState),
        BookOwnershipState::cases()
      );
    }

    $bookOwnershipState = $bookViewFilter->toOwnershipState();
    if ($bookOwnershipState) {
      return [self::composePrivateListClass($user, $bookOwnershipState)];
    }

    return null;
  }

  public static function composePrivateListClass(User $user, BookOwnershipState $bookOwnershipState): string
  {
    $userId = $user->getId() ?? throw new RuntimeException('User has no id');

    return self::BOOK_LIST_CLASS_PREFIX.(string) $userId.'-'.$bookOwnershipState->value;
  }

  public static function composeQuerySelectorOfPrivateList(User $user, BookOwnershipState $bookOwnershipState): string
  {
    return '.'.self::composePrivateListClass($user, $bookOwnershipState);
  }

  public static function publicListClasses(BookViewFilter $bookViewFilter, User $user): ?array
  {
    return match ($bookViewFilter) {
      BookViewFilter::Public => [self::BOOK_LIST_CLASS_PUBLIC],
      BookViewFilter::PublicAbsent => [self::BOOK_LIST_CLASS_PUBLIC, self::composePublicListClass($user)],
      default => null
    };
  }

  public static function composePublicListClass(User $user): string
  {
    $userId = $user->getId() ?? throw new RuntimeException('User has no id');

    return self::BOOK_LIST_CLASS_PUBLIC.'-'.(string) $userId;
  }

  public static function composeQuerySelectorOfPublicListNotBelongingToUser(User $user): string
  {
    return '.'.self::BOOK_LIST_CLASS_PUBLIC.':not(.'.self::composePublicListClass($user).')';
  }
}
