<?php

declare(strict_types=1);

namespace SimpleWebApps\Book;

use RuntimeException;
use SimpleWebApps\Auth\RelationshipCapability;
use SimpleWebApps\Entity\Book;
use SimpleWebApps\Entity\BookOwnership;
use SimpleWebApps\Entity\User;
use SimpleWebApps\EventBus\Event;
use SimpleWebApps\EventBus\EventBusInterface;
use SimpleWebApps\EventBus\EventScope;
use SimpleWebApps\Repository\BookOwnershipRepository;
use SimpleWebApps\Repository\UserRepository;
use Symfony\Component\Uid\Ulid;
use Twig\Environment;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BookBroadcaster
{
  public const TOPIC = 'books';

  private const STREAM_TEMPLATE = 'books/stream.html.twig';

  public function __construct(
    private readonly UserRepository $userRepository,
    private readonly BookOwnershipRepository $bookOwnershipRepository,
    private readonly Environment $twig,
    private readonly EventBusInterface $eventBus,
  ) {
    // Do nothing.
  }

  public function onBookUpdated(Book $book): void
  {
    $content = $this->twig
      ->load(self::STREAM_TEMPLATE)
      ->renderBlock('book_updated', [
        'id' => BookCard::contentHtmlId($book->getId()),
        'bookOwnership' => BookCard::wrapInEmptyOwnership($book),
      ]);

    // Broadcast to all users if the book is public
    if ($book->isPublic()) {
      $this->eventBus->post(new Event([], self::TOPIC, $content, EventScope::AllUsersOfSpecifiedTopic));

      return;
    }

    // Broadcast to all users that have the book in their library, as well as other users that have a relationship with said users
    $bookOwners = array_map(
      fn (BookOwnership $bookOwnership) => $bookOwnership->getOwner() ?? throw new RuntimeException('Book ownership has no owner'),
      $this->bookOwnershipRepository->findBy(['book' => $book])
    );
    $affectedUsers = array_map(
      fn (User $user) => (string) $user->getId(),
      $this->userRepository->getControllingUsersIncludingSelf($bookOwners, RelationshipCapability::Read->permissionsRequired())
    );
    $this->eventBus->post(new Event($affectedUsers, self::TOPIC, $content));
  }

  public function onBookDeleted(Book $book, Ulid $bookId): void
  {
    // Broadcast only if the book was public, since a private book card got automatically removed once the book ownership got removed.
    if ($book->isPublic()) {
      $content = $this->twig
        ->load(self::STREAM_TEMPLATE)
        ->renderBlock('book_deleted', [
          'id' => BookCard::cardHtmlId($bookId),
        ]);
      $this->eventBus->post(new Event([], self::TOPIC, $content, EventScope::AllUsersOfSpecifiedTopic));
    }
  }
}
