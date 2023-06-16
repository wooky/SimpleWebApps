<?php

declare(strict_types=1);

namespace SimpleWebApps\Book;

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
  public static function privateListClasses(string $bookViewFilter, User $user): ?array
  {
    $privateClass = self::composePrivateClass($user);

    if (BookViewFilter::All->value === $bookViewFilter) {
      $classes = array_map(
        fn (BookOwnershipState $bookOwnershipState) => self::composePrivateListClass($user, $bookOwnershipState),
        BookOwnershipState::cases(),
      );
      $classes[] = $privateClass;

      return $classes;
    }

    $bookOwnershipState = BookOwnershipState::tryFrom($bookViewFilter);
    if ($bookOwnershipState) {
      return [$privateClass, self::composePrivateListClass($user, $bookOwnershipState)];
    }

    return null;
  }

  public static function composePrivateClass(User $user, string $state = 'private'): string
  {
    $userId = $user->getId();

    return self::BOOK_LIST_CLASS_PREFIX.(string) $userId.'-'.$state;
  }

  public static function composePrivateListClass(User $user, BookOwnershipState $bookOwnershipState): string
  {
    return self::composePrivateClass($user, $bookOwnershipState->value);
  }

  public static function composeQuerySelectorOfPrivateList(User $user, BookOwnershipState $bookOwnershipState): string
  {
    return '.'.self::composePrivateListClass($user, $bookOwnershipState);
  }

  public static function publicListClasses(string $bookViewFilter, User $user): ?array
  {
    return match (BookViewFilter::tryFrom($bookViewFilter)) {
      BookViewFilter::Public => [self::BOOK_LIST_CLASS_PUBLIC],
      BookViewFilter::PublicAbsent => [self::BOOK_LIST_CLASS_PUBLIC, self::composePublicListClass($user)],
      default => null,
    };
  }

  public static function composePublicListClass(User $user): string
  {
    $userId = $user->getId();

    return self::BOOK_LIST_CLASS_PUBLIC.'-'.(string) $userId;
  }

  public static function composeQuerySelectorOfPublicListNotBelongingToUser(User $user): string
  {
    return '.'.self::BOOK_LIST_CLASS_PUBLIC.':not(.'.self::composePublicListClass($user).')';
  }
}
